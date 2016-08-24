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
 * Validate environment variables
 */
try {
    if (!isset($_SERVER['argv']) || 6 !== count($_SERVER['argv'])) {
        $workflow->result()
            ->uid('missing-vars')
            ->title('Missing environment variables')
            ->subtitle('Too little or no arguments given')
            ->valid(false);

    } else {
        $argv = $_SERVER['argv'];
        $requiredEnvVars = getVarMap();
        $missingEnvVars = getMissingRequiredVars($requiredEnvVars, $argv);

        if (count($missingEnvVars)) {
            $workflow->result()
                ->uid('missing-vars')
                ->title('Missing environment variables')
                ->subtitle(implode(', ', $missingEnvVars))
                ->valid(false);

        } else {
            $getEnvVar = getEnvVar($argv, $requiredEnvVars);
            $query = $getEnvVar('QUERY');
            $token = getToken($argv);
            $projects = filterProjects(getProjects($token), $query);

            if (!count($projects)) {
                $workflow->result()
                    ->uid('no-results')
                    ->valid(false)
                    ->title('Project not found')
                    ->type('default');
            } else {
                foreach ($projects as $project) {
                    $workflow->result()
                        ->uid($project['id'])
                        ->title($project['name'])
                        ->subtitle($project['body'])
                        ->autocomplete($project['name'])
                        ->type('default')
                        ->valid(true)
                        ->arg(
                            $getEnvVar('AC_SELFHOSTED_URL') . $project['url_path']
                        );
                }
            }
        }
    }
} catch (Exception $e) {
    $workflow->result()
        ->valid(false)
        ->title('Unknown error')
        ->subtitle('Turn on debug to figure out what\'s wrong');
}

echo $workflow->output();
