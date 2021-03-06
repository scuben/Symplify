<?php declare(strict_types=1);

namespace Symplify\Statie\Tests\Renderable;

use Nette\Utils\FileSystem;
use PHPUnit\Framework\TestCase;
use Symplify\Statie\Configuration\Configuration;
use Symplify\Statie\DependencyInjection\ContainerFactory;
use Symplify\Statie\FileSystem\FileFinder;
use Symplify\Statie\Renderable\RenderableFilesProcessor;

final class RenderableFilesProcessorTest extends TestCase
{
    /**
     * @var string
     */
    private $outputDirectory = __DIR__ . '/RenderFilesProcessorSource/output';

    /**
     * @var string
     */
    private $sourceDirectory = __DIR__ . '/RenderFilesProcessorSource/source';

    /**
     * @var RenderableFilesProcessor
     */
    private $renderableFilesProcessor;

    /**
     * @var FileFinder
     */
    private $fileFinder;

    protected function setUp(): void
    {
        $container = (new ContainerFactory())->create();

        $this->renderableFilesProcessor = $container->get(RenderableFilesProcessor::class);
        $this->fileFinder = $container->get(FileFinder::class);

        /** @var Configuration $configuration */
        $configuration = $container->get(Configuration::class);
        $configuration->setSourceDirectory($this->sourceDirectory);
        $configuration->setOutputDirectory($this->outputDirectory);
    }

    protected function tearDown(): void
    {
        FileSystem::delete(__DIR__ . '/RenderFilesProcessorSource/output');
    }

    public function test(): void
    {
        $fileInfos = $this->fileFinder->findRestOfRenderableFiles($this->sourceDirectory);
        $this->renderableFilesProcessor->processFileInfos($fileInfos);

        $this->assertFileExists($this->outputDirectory . '/contact-me.html');
        $this->assertFileEquals(
            __DIR__ . '/RenderFilesProcessorSource/contact-expected.html',
            $this->outputDirectory . '/contact-me.html'
        );
    }
}
