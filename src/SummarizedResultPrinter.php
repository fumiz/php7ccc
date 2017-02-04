<?php
namespace Fumizp\php7ccc;


use Sstalle\php7cc\CLIOutputInterface;
use Sstalle\php7cc\CompatibilityViolation\CheckMetadata;
use Sstalle\php7cc\CompatibilityViolation\ContextInterface;
use Sstalle\php7cc\CompatibilityViolation\Message;
use Sstalle\php7cc\Error\CheckError;
use Sstalle\php7cc\ResultPrinterInterface;
use PhpParser\PrettyPrinter\Standard as StandardPrettyPrinter;

class SummarizedResultPrinter implements ResultPrinterInterface
{
    /**
     * @var CLIOutputInterface
     */
    private $output;

    /**
     * @var StandardPrettyPrinter
     */
    private $prettyPrinter;

    /**
     * @var Message[]
     */
    private $messages = [];

    /**
     * @var CheckError[]
     */
    private $errors = [];

    /**
     * @var ContextInterface[]
     */
    private $contexts = [];

    /**
     * @param CLIOutputInterface    $output
     * @param StandardPrettyPrinter $prettyPrinter
     */
    public function __construct(
        CLIOutputInterface $output,
        StandardPrettyPrinter $prettyPrinter
    ) {
        $this->output = $output;
        $this->prettyPrinter = $prettyPrinter;
    }

    /**
     * @param ContextInterface $context
     */
    public function printContext(ContextInterface $context)
    {
        if (!$context->hasMessagesOrErrors()) {
            return;
        }

        foreach ($context->getErrors() as $error) {
            $this->output->writeln('[error]  ' . $error->getRawText());
            $this->errors[] = $error;
        }

        foreach ($context->getMessages() as $message) {
            $this->messages[] = $message;
        }
        $this->addFile($context);
    }

    /**
     * @param CheckMetadata $metadata
     */
    public function printMetadata(CheckMetadata $metadata)
    {
    }

    private function addFile(ContextInterface $context)
    {
        $filePath = $context->getCheckedResourceName();
        if ($filePath === false) {
            return;
        }
        if (array_key_exists($filePath, $this->contexts)) {
            assert("don't allow duplicated files");
        }
        $this->contexts[$filePath] = $context;
    }

    public function summarize()
    {
        $this->output->writeln('errors');
        $this->output->writeln('--------------------');
        $sortedMessages = $this->getSortedErrors();
        foreach($sortedMessages as $message) {
            $this->output->writeln(sprintf("%d\t%d\t%s", $message['score'], $message['level'], $message['error']));
        }
        $this->output->writeln('messages');
        $this->output->writeln('--------------------');
        $sortedMessages = $this->getSortedMessages();
        foreach($sortedMessages as $message) {
            $this->output->writeln(sprintf("%d\t%s", $message['score'], $message['message']));
        }
        $this->output->writeln('');
        $this->output->writeln('files');
        $this->output->writeln('--------------------');
        $sortedContexts = $this->getSortedProblemFiles();
        foreach($sortedContexts as $context) {
            /**
             * @var ContextInterface $context
             */
            $this->output->writeln(sprintf("%d\t%s", count($context->getMessages()), $context->getCheckedResourceName()));
        }
    }

    public function getSortedProblemFiles()
    {
        $sortedContexts = $this->contexts;
        usort($sortedContexts, function(ContextInterface $aContext, ContextInterface $bContext){
            $a = count($aContext->getMessages());
            $b = count($bContext->getMessages());
            if ($a === $b) {
                return 0;
            }
            return ($a > $b) ? -1 : 1;
        });
        return $sortedContexts;
    }

    public function getSortedMessages()
    {
        $sortedMessages = [];
        foreach ($this->messages as $message) {
            $rawText = $message->getRawText();
            if (!array_key_exists($rawText, $sortedMessages)) {
                $sortedMessages[$rawText] = [
                    'score' => 0,
                    'message' => $rawText,
                    'level' => $message->getLevel(),
                    'messages' => [],
                ];
            }
            $sortedMessages[$rawText]['score']++;
            $sortedMessages[$rawText]['messages'][] = $message;
        }
        usort($sortedMessages, function($aMessage, $bMessage){
            $a = $aMessage['score'];
            $b = $bMessage['score'];
            if ($a === $b) {
                return 0;
            }
            return ($a > $b) ? -1 : 1;
        });
        return $sortedMessages;
    }

    public function getSortedErrors()
    {
        $sortedErrors = [];
        foreach ($this->errors as $error) {
            $rawText = $error->getRawText();
            if (!array_key_exists($rawText, $sortedErrors)) {
                $sortedErrors[$rawText] = [
                    'score' => 0,
                    'error' => $rawText,
                    'errors' => [],
                ];
            }
            $sortedErrors[$rawText]['score']++;
            $sortedErrors[$rawText]['errors'][] = $error;
        }
        usort($sortedErrors, function($aError, $bError){
            $a = $aError['score'];
            $b = $bError['score'];
            if ($a === $b) {
                return 0;
            }
            return ($a > $b) ? -1 : 1;
        });
        return $sortedErrors;

    }
}
