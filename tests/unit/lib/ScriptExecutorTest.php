<?php
/**
 * Class ScriptExecutorTest
 *
 * @author Maksim Rodikov
 */
declare(strict_types=1);

use \org\bovigo\vfs\vfsStreamFile;
use \PHPUnit\Framework\MockObject\MockObject;
use \PHPUnit\Framework\TestCase;
use \org\bovigo\vfs\vfsStream;
use \whotrades\RdsSystem\lib\CommandExecutor;
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
        $executor = $this->getScriptExecutorMock();
        $output = $executor();
        $this->assertEquals("TEST", $output);
    }

    /**
     * @throws ScriptExecutorException
     * @throws CommandExecutorException
     * @throws \whotrades\RdsSystem\lib\Exception\FilesystemException
     */
    public function testDoubleDip()
    {
        $this->expectException(ScriptExecutorException::class);
        $executor = $this->getScriptExecutorMock();
        for ($i = 0; $i<2; ++$i) {
            $executor();
        }
    }

    /**
     * Every getScriptPath call should return unique path
     */
    public function testUniquePaths()
    {
        $basePath = $this->root->url() . '/test';
        $executor = new ScriptExecutor("TEST", $basePath);
        $path1 = $executor->getScriptPath();
        $path2 = $executor->getScriptPath();
        $this->assertStringStartsWith($basePath, $path1);
        $this->assertStringStartsWith($basePath, $path2);
        $this->assertNotEquals($path1, $path2);
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
        $executor = $this->getScriptExecutorMock();
        $this->root->chmod(000);
        $executor();
    }

    /**
     * @throws CommandExecutorException
     * @throws FilesystemException
     * @throws ScriptExecutorException
     */
    public function testErrorFileNotWritable()
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionCode(FilesystemException::ERROR_WRITE_FILE);
        $executor = $this->getScriptExecutorMock();
        $file = new vfsStreamFile("test.sh", 0000);
        $this->root->addChild($file);
        @$executor(); // We don't want standard stream write error to appear
    }

    public function testErrorFileNotChmoddable()
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionCode(FilesystemException::ERROR_PERMISSIONS);
        $executor = $this->getScriptExecutorMock();
        $file = new vfsStreamFile("test.sh", 0777);
        $file->chown(42); // File owned by other user so we can write to it but can't change permissions
        $this->root->addChild($file);
        $executor();
    }

    /**
     * @throws CommandExecutorException
     * @throws FilesystemException
     * @throws ScriptExecutorException
     */
    public function testErrorCommandException()
    {
        $this->expectException(ScriptExecutorException::class);
        $commandExecutorMock = $this->getMockBuilder(CommandExecutor::class)->getMock();
        $commandExecutorMock
            ->method('executeCommand')
            ->will($this->throwException(
                new CommandExecutorException('TEST', 'TEST', 0, '')
            ));
        $executor = $this->getScriptExecutorMock($commandExecutorMock);
        echo $executor();
    }

    /**
     * @param CommandExecutor $commandExecutor
     *
     * @return MockObject | ScriptExecutor
     */
    private function getScriptExecutorMock(CommandExecutor $commandExecutor = null): MockObject
    {
        $commandExecutor = $commandExecutor ?? $this->getCommandExecutorMock();
        $mock = $this->getMockBuilder(ScriptExecutor::class)
            ->onlyMethods(['getCommandExecutor', 'getScriptPath'])
            ->setConstructorArgs(['TEST', $this->root->url() . '/test'])
            ->getMock();
        $mock->method('getCommandExecutor')->will($this->returnValue($commandExecutor));
        $mock->method('getScriptPath')->will($this->returnValue($this->root->url() . '/test.sh'));
        return $mock;
    }

    /**
     * @return MockObject | CommandExecutor
     */
    private function getCommandExecutorMock(): MockObject
    {
        $mock = $this->getMockBuilder(CommandExecutor::class)->getMock();
        $mock->method('executeCommand')->will($this->returnValue('TEST'));
        return $mock;
    }

}