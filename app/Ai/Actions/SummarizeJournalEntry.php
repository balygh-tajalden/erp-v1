<?php

declare(strict_types=1);

namespace App\Ai\Actions;

use Pixelworxio\LaravelAiAction\Actions\RunAgentAction;
use Pixelworxio\LaravelAiAction\Concerns\InteractsWithAgent;
use Pixelworxio\LaravelAiAction\Contracts\AgentAction;
use Pixelworxio\LaravelAiAction\DTOs\AgentContext;
use Pixelworxio\LaravelAiAction\DTOs\AgentResult;

final class SummarizeJournalEntry implements AgentAction
{
    use InteractsWithAgent;

    /**
     * Return the system-level instructions for this agent.
     */
    public function instructions(AgentContext $context): string
    {
        return 'You are a professional accountant assistant. Your task is to provide a concise, professional summary of a journal entry based on its details. Explain the purpose of the transaction in a way that is easy to understand, focusing on which accounts were affected and why. Use a professional tone and keep the summary brief (2-3 sentences max). Respond in the same language as the notes provided (usually Arabic).';
    }

    /**
     * Return the user-facing prompt for this agent.
     */
    public function prompt(AgentContext $context): string
    {
        /** @var \App\Models\Entry $entry */
        $entry = $context->record;

        $lines = $entry->details->map(function ($detail) {
            $type = $detail->Amount > 0 ? 'Debit' : 'Credit';
            $amount = abs($detail->Amount);
            $accountName = $detail->account?->AccountName ?? 'Unknown Account';
            return "- {$type}: {$accountName} ({$amount}) " . ($detail->Notes ? " - Notes: {$detail->Notes}" : '');
        })->implode("\n");

        return sprintf(
            "Please summarize this journal entry:\n\nDate: %s\nMain Notes: %s\n\nTransaction Lines:\n%s",
            $entry->TheDate?->format('Y-m-d'),
            $entry->Notes ?? 'No notes provided',
            $lines
        );
    }

    /**
     * Execute the agent action and return a typed AgentResult.
     *
     * Delegate to RunAgentAction::execute() — do not put business logic here.
     * Pre-processing belongs in instructions() and prompt(); Post-processing
     * belongs in callers that consume the returned AgentResult.
     *
     * @param AgentContext $context The runtime context for this invocation.
     * @return AgentResult The typed result wrapping the AI response.
     */
    public function handle(AgentContext $context): AgentResult
    {
        return app(RunAgentAction::class)->execute($this, $context);
    }
	/**
	 */
	function __construct() {
	}
}
