<?php
declare(strict_types = 1);

namespace LanguageServer\Tests\Server\TextDocument\Definition;

use LanguageServer\Tests\MockProtocolStream;
use LanguageServer\Tests\Server\TextDocument\TextDocumentTestCase;
use LanguageServer\{Server, LanguageClient, Project};
use LanguageServer\Protocol\{TextDocumentIdentifier, Position, Range, Location};

class GlobalFallbackTest extends TextDocumentTestCase
{
    public function setUp()
    {
        $client = new LanguageClient(new MockProtocolStream());
        $project = new Project($client);
        $this->textDocument = new Server\TextDocument($project, $client);
        $project->openDocument('global_fallback', file_get_contents(__DIR__ . '/../../../../fixtures/global_fallback.php'));
        $project->openDocument('global_symbols', file_get_contents(__DIR__ . '/../../../../fixtures/global_symbols.php'));
    }

    public function testClassDoesNotFallback()
    {
        // $obj = new TestClass();
        // Get definition for TestClass should not fall back to global
        $result = $this->textDocument->definition(new TextDocumentIdentifier('global_fallback'), new Position(9, 16));
        $this->assertEquals([], $result);
    }

    public function testFallsBackForConstants()
    {
        // echo TEST_CONST;
        // Get definition for TEST_CONST
        $result = $this->textDocument->definition(new TextDocumentIdentifier('global_fallback'), new Position(6, 10));
        $this->assertEquals(new Location('global_symbols', new Range(new Position(4, 6), new Position(4, 22))), $result);
    }

    public function testFallsBackForFunctions()
    {
        // test_function();
        // Get definition for test_function
        $result = $this->textDocument->definition(new TextDocumentIdentifier('global_fallback'), new Position(5, 6));
        $this->assertEquals(new Location('global_symbols', new Range(new Position(33, 0), new Position(36, 1))), $result);
    }
}