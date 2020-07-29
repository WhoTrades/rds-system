<?php
/**
 * Class ScriptExecutorTest
 *
 * @author Maksim Rodikov
 */
declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use \PHPUnit\Framework\TestCase;
use \org\bovigo\vfs\vfsStream;
use \whotrades\RdsSystem\lib\Exception\CommandExecutorException;
use \whotrades\RdsSystem\lib\Exception\FilesystemException;
use \whotrades\RdsSystem\lib\Exception\ScriptExecutorException;
use \whotrades\RdsSystem\lib\ScriptExecutor;

class ScriptExecutorTest extends TestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory  */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function tearDown(): void
    {
        $this->root = null;
    }

    /**
     * @throws ScriptExecutorException
     * @throws CommandExecutorException
     * @throws \whotrades\RdsSystem\lib\Exception\FilesystemException
     */
    public function testScriptExecutor()
    {
        $executor = $this->getScriptExecutor();
        $output = $executor();
        $this->assertEquals("TEST",$output);
    }

    /**
     * @throws ScriptExecutorException
     * @throws CommandExecutorException
     * @throws \whotrades\RdsSystem\lib\Exception\FilesystemException
     */
    public function testDoubleDip()
    {
        $this->expectException(ScriptExecutorException::class);
        $executor = $this->getScriptExecutor();
        for ($i = 0; $i<2; ++$i) {
            $executor();
        }
    }

    /**
     * @throws FilesystemException
     * @throws ScriptExecutorException
     * @throws CommandExecutorException
     */
    public function testErrorDirectoryNotWritable()
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionCode(FilesystemException::ERROR_WRITE_DIRECTORY);
        $executor = $this->getScriptExecutor();
        $this->root->chmod(000);
        $executor();
    }

    public function testErrorFileNotWritable()
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionCode(FilesystemException::ERROR_WRITE_FILE);
        /** @var MockObject | ScriptExecutor $executor */
        $executor = $this->getMockBuilder(ScriptExecutor::class)
            ->setConstructorArgs([
                "echo TEST",
                $this->root->url(),
            ])
            ->onlyMethods([
                'getScriptPath'
            ])
            ->getMock();
        $lockedFile = new \org\bovigo\vfs\vfsStreamFile("testfile", 0000);
        $this->root->addChild($lockedFile);
        $executor->method('getScriptPath')->will($this->returnValue($lockedFile->url()));
        @$executor(); // We don't want standard stream write error to appear
    }


    private function getScriptExecutor(): ScriptExecutor
    {
        return new ScriptExecutor("echo TEST", $this->root->url() . "/test");
    }

}