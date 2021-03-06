<?php declare(strict_types=1);

namespace Symplify\Statie\FileSystem;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class FileFinder
{
    /**
     * @var string[]
     */
    private $staticFileExtensions = [
        'png', 'jpg', 'svg', 'css', 'ico', 'js', '', 'jpeg', 'gif', 'zip', 'tgz', 'gz',
        'rar', 'bz2', 'pdf', 'txt', 'tar', 'mp3', 'doc', 'xls', 'pdf', 'ppt', 'txt', 'tar', 'bmp', 'rtf', 'woff2',
        'woff', 'otf', 'ttf', 'eot',
    ];

    /**
     * @return SplFileInfo[]
     */
    public function findInDirectory(string $sourceDirectory): array
    {
        return $this->findInDirectoryByMask($sourceDirectory, '*');
    }

    /**
     * @return SplFileInfo[]
     */
    public function findLatteLayoutsAndSnippets(string $directory): array
    {
        $finder = Finder::create()->files()
            ->in($directory)
            ->path('#(_layouts|_snippets)#');

        return $this->getFilesFromFinder($finder);
    }

    /**
     * @return SplFileInfo[]
     */
    public function findStaticFiles(string $directory): array
    {
        $staticFileMask = '*' . implode(',*', $this->staticFileExtensions);

        return $this->findInDirectoryByMask($directory, $staticFileMask);
    }

    /**
     * @return SplFileInfo[]
     */
    public function findRestOfRenderableFiles(string $directory): array
    {
        $finder = Finder::create()->files()
            ->name('*.html')
            ->name('*.latte')
            ->name('*.rss')
            ->name('*.xml')
            ->notName('_layout')
            ->notName('_snippets')
            ->in($directory);

        return $this->getFilesFromFinder($finder);
    }

    /**
     * @return SplFileInfo[]
     */
    private function findInDirectoryByMask(string $directory, string $mask): array
    {
        $finder = Finder::create()->files()
            ->name($mask)
            ->in($directory)
            // sort by name descending
            ->sort(function (SplFileInfo $firstFileInfo, SplFileInfo $secondFileInfo) {
                return strcmp($secondFileInfo->getRealPath(), $firstFileInfo->getRealPath());
            });

        return $this->getFilesFromFinder($finder);
    }

    /**
     * @return SplFileInfo[]
     */
    private function getFilesFromFinder(Finder $finder): array
    {
        $files = [];
        foreach ($finder->getIterator() as $key => $file) {
            $files[$key] = $file;
        }

        return $files;
    }
}
