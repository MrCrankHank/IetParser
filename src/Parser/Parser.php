<?php

/**
 * This file contains the Parser class.
 *
 * PHP version 5.6
 *
 * @category Parser
 *
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @link     null
 */
namespace MrCrankHank\IetParser\Parser;

use Illuminate\Support\Collection;
use MrCrankHank\IetParser\Exceptions\NotFoundException;
use MrCrankHank\IetParser\Interfaces\FileInterface;
use MrCrankHank\IetParser\Interfaces\ParserInterface;

/**
 * Class Parser.
 *
 * @category Parser
 *
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @link     null
 */
abstract class Parser implements ParserInterface
{
    /**
     * Contains the extracted comments
     * of the file.
     *
     * @var
     */
    protected $comments;

    /**
     * Contains the file content
     * without any modifications.
     *
     * @var
     */
    protected $originalContent;

    /**
     * Contains the iqn.
     *
     * @var
     */
    protected $target;

    /**
     * Line of target inside in the $this->fileContent collection.
     *
     * @var bool|mixed
     */
    protected $targetId;

    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * @var
     */
    protected $fileContent;

    /**
     * Parser constructor.
     *
     * @param FileInterface $file
     * @param string        $target IQN
     */
    public function __construct(FileInterface $file, $target = null)
    {
        $this->file = $file;

        $this->target = $target;

        $this->fileContent = $this->read();
    }

    /**
     * Retrieves the file's content without any comments or newlines.
     *
     * @return Collection
     */
    public function read()
    {
        $fileContent = $this->readRaw();

        $fileContent = $this->_handleComments($fileContent);

        return $fileContent;
    }

    /**
     * Retrieves the file's content exactly as it is.
     *
     * @return Collection
     */
    public function readRaw()
    {
        $fileContent = $this->file->refresh()->getContent();

        $this->originalContent = $fileContent;

        return collect(explode("\n", $fileContent));
    }

    /**
     * Merge the file's content with comments
     * and new lines and write it back.
     *
     * @return void
     */
    public function write()
    {
        // convert collections to arrays
        $fileContent = $this->fileContent->all();
        $comments = $this->comments->all();

        if (isset($fileContent['new'])) {
            // save new line to variable and delete it from the array
            // so ksort can sort the indexes numerically
            $new = $fileContent['new'];
            unset($fileContent['new']);
        }

        // merge config with comments
        $fileContent = $fileContent + $comments;

        // sort the array, so the lines are correct
        ksort($fileContent);

        if (!empty($new)) {
            // push the new line as first item
            array_unshift($fileContent, $new);
        }

        $fileContent = implode("\n", $fileContent);

        $this->file->getFilesystem()->update($this->file->getFilePath(), $fileContent);
    }

    /**
     * Write a raw string as file.
     *
     * @param string $string String to be written
     *
     * @return void
     */
    public function writeRaw($string)
    {
        $this->file->getFilesystem()->update($this->file->getFilePath(), $string);
    }

    /**
     * Reread the files content.
     *
     * @return void
     */
    public function refresh()
    {
        $this->fileContent = $this->read();
    }

    /**
     * Extract comments from the file.
     *
     * @param Collection $fileContent Collection of the file's content
     *
     * @return Collection
     */
    private function _handleComments(Collection $fileContent)
    {
        $fileContent = $fileContent->filter(function ($line, $key) {
            if (empty($line)) {
                // save empty lines in comments array
                $this->comments[$key] = $line;

                return false;
            }

            // check for comments
            $offset = stripos(preg_replace('/\s+/', '', $line), '#');
            if ($offset !== false) {
                // extract the whole line if it's commented
                $this->comments[$key] = $line;

                return false;
            }

            return true;
        });

        $this->comments = collect($this->comments);

        // Flip collection to preserve the indexes
        return $fileContent;
    }

    /**
     * Find a specific global option.
     *
     * @param Collection $fileContent Collection of the file's content
     * @param string     $option      Option to be found
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    protected function findGlobalOption(Collection $fileContent, $option)
    {
        $id = $this->findFirstTargetDefinition($fileContent);

        // decrement id
        // so we get the last global line
        $id--;

        for ($i = 0; $i <= $id; $i++) {
            if ($fileContent->has($i) && $fileContent->get($i) === $option) {
                return $i;
            }

            // So here we are, last line
            // This means we didn't find the index
            // So let's throw an exception here and go home
            if ($i === $id) {
                return false;
            }
        }

        return false;
    }

    /**
     * Return the id of the first target definition.
     *
     * @param Collection $fileContent Collection of the file's content
     *
     * @return mixed
     */
    protected function findFirstTargetDefinition(Collection $fileContent)
    {
        $firstTarget = $fileContent->first(function ($value, $key) {
            if (substr($value, 0, 6) === 'Target') {
                return true;
            }
        });

        return $fileContent->search($firstTarget, true);
    }
}
