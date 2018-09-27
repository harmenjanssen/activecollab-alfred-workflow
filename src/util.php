<?php
class WorkflowException extends \Exception {
    public $subtitle = '';
    public $uid = '';
}

function writeCache($key, $data) {
    file_put_contents(WORKFLOW_ROOT . "/cache/$key", serialize($data));
}

function readCache($key) {
    if (file_exists(WORKFLOW_ROOT . "/cache/$key")) {
        return unserialize(file_get_contents(WORKFLOW_ROOT . "/cache/$key"));
    }
    return null;
}

function clearCache() {

}

function getVarMap() {
    return [
        'QUERY' => 1,
        'AC_USERNAME' => 2,
        'AC_PASSWORD' => 3,
        'AC_ORG_NAME' => 4,
        'AC_SELFHOSTED_URL' => 5
    ];
}

function getMissingRequiredVars(array $requiredEnvVars, array $argv) {
    $missingEnvVars = array_filter(array_keys($requiredEnvVars),
        function ($var) use ($requiredEnvVars, $argv) {
            return !isset($argv[$requiredEnvVars[$var]]) || !$argv[$requiredEnvVars[$var]];
        }
    );
    return $missingEnvVars;
}

function getEnvVar($argv, $envVars, $key = null) {
    if (func_num_args() === 2) {
        // crudely partially applying this shit
        return function ($key) use ($argv, $envVars) {
            return getEnvVar($argv, $envVars, $key);
        };
    }
    return $argv[$envVars[$key]];
}

function getToken($argv) {
    // Issue a token.
    if (!$token = readCache('API_TOKEN')) {
        $requiredEnvVars = getVarMap();
        $authenticator = new \ActiveCollab\SDK\Authenticator\SelfHosted(
            getEnvVar($argv, $requiredEnvVars, 'AC_ORG_NAME'),
            'Alfred workflow',
            getEnvVar($argv, $requiredEnvVars, 'AC_USERNAME'),
            getEnvVar($argv, $requiredEnvVars, 'AC_PASSWORD'),
            getEnvVar($argv, $requiredEnvVars, 'AC_SELFHOSTED_URL')
        );

        $token = $authenticator->issueToken();
        writeCache('API_TOKEN', $token);
    }
    return $token;
}

function projectIsActive(array $project) {
    return !$project['is_completed'] && !$project['is_trashed'];
}

function getProjects($token) {
    if ($projects = readCache('AC_PROJECTS')) {
        return $projects;
    }
    $client = new \ActiveCollab\SDK\Client($token);

    $projects = json_decode($client->get('projects')->getBody(), true);
    writeCache('AC_PROJECTS', $projects);
    return $projects;
}

function filterProjects($projects, $query) {
    $projects = array_filter($projects, 'projectIsActive');
    return array_filter($projects, function ($proj) use ($query) {
        return fuzzyMatch($query, $proj['name']);
    });
}

function getProjectId($token, $projectName) {
    $projects = getProjects($token);
    $matches = array_filter($projects, function ($proj) use ($projectName) {
        return $projectName === $proj['name'];
    });
    return current($matches)['id'] ?? 0;
}

function getTasks($token, $id) {
    $client = new \ActiveCollab\SDK\Client($token);
    return json_decode($client->get("projects/{$id}/tasks")->getBody(), true)['tasks'] ?? [];
}

function filterTasks($tasks, $query) {
    return array_filter($tasks, function ($task) use ($query) {
        if (is_numeric($query)) {
            return fuzzyMatch($query, $task['task_number']);
        }
        return fuzzyMatch($query, $task['name']);
    });
}

function halt($uid, $message, $subtitle = '') {
    $exception = new WorkflowException($message);
    $exception->uid = $uid;
    $exception->subtitle = $subtitle;
    throw $exception;
}

/**
 * Matches when the chars of $search appear in the same order in $check.
 */
function fuzzyMatch($search, $check, $caseInsensitive = true) {
    if ($caseInsensitive) {
        $search = strtolower($search);
        $check = strtolower($check);
    }
    $last_pos = 0;
    for ($j = 0, $l = strlen($search); $j < $l; ++$j) {
        $c = $search[$j];
        $p = strpos($check, $c, $last_pos);
        if (false === $p) {
            return false;
        }
        $last_pos = $p;
    }
    return true;
}


