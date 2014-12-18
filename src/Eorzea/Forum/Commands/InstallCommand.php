<?php namespace Eorzea\Forum\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command {

	protected $name        = 'forum:install';
	protected $description = 'Create default forum controllers.';

	public function fire()
	{
		$this->info('Fetching controller names from integration config...');
		$viewController = class_basename(\Config::get('forum::integration.viewcontroller'));
		$postController = class_basename(\Config::get('forum::integration.postcontroller'));

		$this->info('Config specify controllers "'.$viewController.'" and "'.$postController.'"');
		if (!$this->confirm('Proceed with creation of controllers (no override)? [Yes|no]'))
		{
			$this->info('Action aborted. No changes done.');
			return 1;
		}

		$this->installController($viewController, '\Eorzea\Forum\Controllers\AbstractViewForumController');
		$this->installController($postController, '\Eorzea\Forum\Controllers\AbstractPostForumController');

		$this->info('Forum installation done.');
		return 0;
	}

	private function installController($controllerName, $sourceController)
	{
		$controllerFile = $this->laravel->path.'/controllers/'.$controllerName.'.php';
		if(file_exists($controllerFile))
		{
			$this->info('File app/controllers/'.$controllerName.' Exists. Action aborted.');
			return 1;
		}

		file_put_contents($controllerFile, $this->getControllerContent($controllerName, $sourceController));
		$this->info('File app/controllers/'.$controllerName.' Created');
	}

	public function getControllerContent($controllerName, $sourceController)
	{
		$content = "<?php\n"
		."/* Autogenerated Forum Controller */\n"
		."/* Hook point of the Forum package inside your laravel application */\n"
		."/* Feel free to override methods here to fit your requirements */\n"
		."class %s extends %s {\n\n"
		."}\n";

		return sprintf($content, $controllerName, $sourceController);
	}

}
