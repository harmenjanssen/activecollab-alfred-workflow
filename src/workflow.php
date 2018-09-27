<?php
/**
 * Alfred 3 ActiveCollab workflow
 *
 * @author Harmen Janssen <harmen@whatstyle.net>
 */
define('WORKFLOW_ROOT', dirname(__FILE__));
require_once WORKFLOW_ROOT . '/vendor/autoload.php';
require_once WORKFLOW_ROOT . '/util.php';

$workflow = new Alfred\Workflows\Workflow;

/**
 * Here we go, any exception are turned into invalid results,
 * nicely displayed with a title and subtitle – if applicable.
 */
try {
    /**
     * Validate environment variables
     */
    $argv = $_SERVER['argv'] ?? [];
    $requiredEnvVars = getVarMap();
    $missingEnvVars = getMissingRequiredVars($requiredEnvVars, $argv);

    if (count($missingEnvVars)) {
        halt('missing-vars', 'Missing environment variables', implode(', ', $missingEnvVars));
    }

    $getEnvVar = getEnvVar($argv, $requiredEnvVars);
    $token = getToken($argv);

    /**
     * Parse query
     */
    $query = $getEnvVar('QUERY');
    $queryBits = explode(' > ', $query);
    if (count($queryBits) === 1) {
        $projects = filterProjects(getProjects($token), $query);

        if (!count($projects)) {
            halt('no-results', 'Project not found');
        }
        foreach ($projects as $project) {
            $workflow->result()
                     ->uid($project['id'])
                     ->title($project['name'])
                     ->subtitle($project['body'])
                     ->autocomplete($project['name'] . ' > ')
                     ->type('default')
                     ->valid(true)
                     ->arg(
                         $getEnvVar('AC_SELFHOSTED_URL') . $project['url_path']
                     );
        }
    } else {
        $projectName = $queryBits[0];
        $projectId = getProjectId($token, $projectName);
        if (!$projectId) {
            halt('invalid-project', 'Invalid project', '"'.$projectName . '" ' . $projectId);
        }
        $taskQuery = $queryBits[1];
        $tasks = filterTasks(getTasks($token, $projectId), $taskQuery);

        if (!count($tasks)) {
            halt('no-tasks', 'No tasks found', $taskQuery);
        }
        foreach ($tasks as $task) {
            $workflow->result()
                     ->uid($task['id'])
                     ->title($task['name'])
                     ->subtitle('bananarama')
                     ->autocomplete($id . ' > ' . $task['name'])
                     ->type('default')
                     ->valid(true)
                     ->arg(
                         $getEnvVar('AC_SELFHOSTED_URL') . $task['url_path']
                     );
        }
    }
} catch (WorkflowException $e) {
    $workflow->result()
             ->uid($e->uid)
             ->title($e->getMessage())
             ->subtitle($e->subtitle)
             ->valid(false);
} catch (Exception $e) {
    $workflow->result()
             ->valid(false)
             ->title('Unknown error')
             ->subtitle('Turn on debug to figure out what\'s wrong');
}

echo $workflow->output();

