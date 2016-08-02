<?php

namespace MrCrankHank\IetParser\Parser;

use MrCrankHank\IetParser\Exceptions\DuplicationErrorException;
use MrCrankHank\IetParser\Exceptions\NotFoundException;

/**
 * Class GlobalOption
 *
 * Add/delete global options to/from a iet config file.
 * Global options are similar to target specific options.
 * But they are defined before any target definition
 *
 * @package MrCrankHank\IetParser\Parser;
 */
class GlobalOptionParser extends Parser
{
    /**
     * Add a global line
     *
     * @throws DuplicationErrorException
     * @param $option
     * @return $this
     */
    public function add($option)
    {
        $fileContent = $this->get();

        $id = $this->findGlobalOption($fileContent, $option);

        // Check if the option is already defined
        if ($id === false) {
            $fileContent->prepend($option, 'new');
        } else {
            throw new DuplicationErrorException('The option ' . $option . ' is already set.');
        }

        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * Remove a global line
     *
     * Don't remove target definitions using this function, because it does not take care of target options
     *
     * @throws NotFoundException
     * @param $option
     * @return $this
     */
    public function delete($option)
    {
        $fileContent = $this->get();

        $id = $this->findFirstTargetDefinition($fileContent);

        // decrement id
        // so we get the last global line
        $id--;

        for($i = 0; $i < $id; $i++) {
            if ($fileContent->has($id)) {
                if ($fileContent->get($id) === $option) {
                    $fileContent->forget($id);
                    break;
                }

                // So here we are, last line
                // This means we didn't find the index
                // So let's throw an exception here and go home
                if ($i === $id) {
                    throw new NotFoundException('The option ' . $option . ' was not found');
                }
            }
        }

        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * Convenience wrapper for adding a incoming user
     *
     * @param $username
     * @param $password
     * @return $this
     */
    public function addIncomingUser($username, $password)
    {
        return $this->add('IncomingUser ' . $username . ' ' . $password);
    }

    /**
     * Convenience wrapper for adding a outgoing user
     *
     * @param $username
     * @param $password
     * @return $this
     */
    public function addOutgoingUser($username, $password)
    {
        return $this->add('OutgoingUser ' . $username . ' ' . $password);
    }

    public function removeIncomingUser()
    {

    }

    public function removeOutgoingUser()
    {

    }

    /**
     * Validate the global option according to the iet man page
     */
    protected function validate()
    {

    }
}