<?php declare(strict_types = 1);

namespace Tests\Nettrine\Migrations\DI;

use Doctrine\DBAL\Migrations\Tools\Console\Command\AbstractCommand;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Migrations\ContainerAwareConfiguration;
use Nettrine\Migrations\DI\MigrationsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

final class MigrationsExtensionTest extends TestCase
{

	/**
	 * @return void
	 */
	public function testConsole(): void
	{
		$loader = new ContainerLoader(__DIR__ . '/temp', TRUE);
		$class = $loader->load(function (Compiler $compiler): void {
			// Required services and params
			$compiler->loadConfig(__DIR__ . '/config/services.neon');
			// Migrations
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->loadConfig(__DIR__ . '/config/default.neon');
		}, 1);

		/** @var Container $container */
		$container = new $class();

		/** @var ContainerAwareConfiguration $awareConfiguration */
		$awareConfiguration = $container->getByType(ContainerAwareConfiguration::class);
		self::assertEquals('Migrations', $awareConfiguration->getMigrationsNamespace());
		self::assertEquals('/srv/app/../migrations', $awareConfiguration->getMigrationsDirectory());
		self::assertCount(8, $container->findByType(AbstractCommand::class));
		// 4 default helpers + configurationHelper
		self::assertCount(5, iterator_to_array($container->getByType(Application::class)->getHelperSet()));
	}

	/**
	 * @return void
	 */
	public function testWithoutConsole(): void
	{
		$loader = new ContainerLoader(__DIR__ . '/temp', TRUE);
		$class = $loader->load(function (Compiler $compiler): void {
			// Required services and params
			$compiler->loadConfig(__DIR__ . '/config/services_without_console.neon');
			// Migrations
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->loadConfig(__DIR__ . '/config/default.neon');
		}, 2);

		/** @var Container $container */
		$container = new $class();

		/** @var ContainerAwareConfiguration $awareConfiguration */
		$awareConfiguration = $container->getByType(ContainerAwareConfiguration::class);
		self::assertEquals('Migrations', $awareConfiguration->getMigrationsNamespace());
		self::assertEquals('/srv/app/../migrations', $awareConfiguration->getMigrationsDirectory());
		self::assertCount(8, $container->findByType(AbstractCommand::class));
	}

}
