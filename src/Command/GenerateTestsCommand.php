<?php

namespace Octave\ToolsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Symfony\Component\Filesystem\Filesystem;

class GenerateTestsCommand extends Command
{
    private const TEST_DIR = 'tests/Controller/Generated';

    protected static $defaultName = 'octave:tools:generate-tests';

    private $projectDir;

    private RouterInterface $router;

    private Environment $twig;

    public function __construct($projectDir, RouterInterface $router, Environment $twig)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
        $this->router = $router;
        $this->twig = $twig;

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();
        $testDir = $this->projectDir . '/' . self::TEST_DIR;
        $filesystem->mkdir($testDir);

        $routes = $this->router->getRouteCollection();
        $testMethods = [];
        $generatedTests = 0;

        foreach ($routes as $routeName => $route) {
            $controller = $route->getDefault('_controller');

            if (!in_array('GET', $route->getMethods()) ||
                $route->getOption('no_test') === true ||
                !$controller ||
                !str_starts_with($controller, 'App\Controller') ||
                count($route->compile()->getPathVariables()) > 0) {
                continue;
            }

            $parts = explode(':', $controller);
            if (count($parts) !== 2) {
                continue;
            }

            $controllerParts = explode('\\', $parts[0]);
            $controllerName = str_replace('Controller', '', end($controllerParts));
            $actionName = $parts[1];

            $methodName = sprintf('test%s%s', $controllerName, ucfirst($actionName));

            $testMethods[] = [
                'methodName' => $methodName,
                'routeName' => $routeName,
                'routePath' => $route->getPath(),
                'controller' => $controller,
            ];
            $generatedTests++;
        }

        $content = $this->twig->render('@OctaveTools/test/controller_test.html.twig', [
            'testMethods' => $testMethods
        ]);

        $testFilePath = sprintf('%s/GeneratedControllerTest.php', $testDir);
        file_put_contents($testFilePath, $content);

        $output->writeln(sprintf('Generated %d test methods in %s', $generatedTests, $testFilePath));

        return Command::SUCCESS;
    }
}