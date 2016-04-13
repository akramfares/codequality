<?php

namespace Med\Codequality\Composer\Script;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

class Hooks
{
    const GIT_HOOK_PATH      = '/../../../../../.git/hooks/';
	
	public static $hooks = array(
	    'hook-chain' => '/../../Resources/scripts/hook-chain',
	    'pre-commit.quality' => '/../../Resources/hooks/pre-commit.php',
		'pre-push.quality' => '/../../Resources/hooks/pre-push.php'
	);
	
    public static function setHooks(Event $event)
    {
        $fs = new Filesystem();

        if (false === $event->isDevMode()) {
            return;
        }

        $hookdir = self::getAbsolutePath(self::GIT_HOOK_PATH);

        //nothing to do we are not in a git project
        if (false == $fs->exists($hookdir)) {
            return;
        }
        
        $event->getIO()->write('Installing the CodeQuality HOOKS');
        
        // Rename initial pre-commit script
        exec("mv ".$hookdir."pre-commit ".$hookdir."pre-commit.init");
        exec("mv ".$hookdir."pre-push ".$hookdir."pre-push.init");
        
        // Link hooks to the hook chain script
        exec("ln -s ".$hookdir."hook-chain ".$hookdir."pre-commit");
        exec("ln -s ".$hookdir."hook-chain ".$hookdir."pre-push");
        
		foreach (self::$hooks as $name => $path) {
			$gitHookPath = sprintf('%s%s', $hookdir, $name);

			$gitHook = @file_get_contents($gitHookPath);
			$docHook = @file_get_contents(self::getAbsolutePath($path));

			if ($gitHook !== $docHook) {
				file_put_contents($gitHookPath, $docHook);
				exec("chmod +x ".$gitHookPath);
			}
		}

    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getAbsolutePath($path)
    {
        return __DIR__ . $path;
    }
}
