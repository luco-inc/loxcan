<?php

declare(strict_types=1);

namespace Siketyan\Loxcan\Reporter\GitHub;

use Siketyan\Loxcan\Reporter\EnvironmentTrait;
use Siketyan\Loxcan\Reporter\ReporterInterface;

class GitHubReporter implements ReporterInterface
{
    use EnvironmentTrait;

    public function __construct(
        private readonly GitHubMarkdownBuilder $markdownBuilder,
        private readonly GitHubClient $client,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function report(array $diffs): void
    {
        $owner = $this->getEnv('LOXCAN_REPORTER_GITHUB_OWNER');
        $repo = $this->getEnv('LOXCAN_REPORTER_GITHUB_REPO');
        $issueNumber = (int) $this->getEnv('LOXCAN_REPORTER_GITHUB_ISSUE_NUMBER');
        $username = $this->getEnv('LOXCAN_REPORTER_GITHUB_USERNAME');
        $body = $this->markdownBuilder->build($diffs);

        $comments = $this->client->getComments($owner, $repo, $issueNumber);
        $myComments = array_filter(
            $comments,
            fn (GitHubComment $comment): bool => $comment->getAuthor()->getLogin() === $username,
        );

        if ($myComments !== []) {
            $this->client->updateComment(
                $owner,
                $repo,
                $myComments[array_key_first($myComments)],
                $body,
            );

            return;
        }

        $this->client->createComment($owner, $repo, $issueNumber, $body);
    }

    public function supports(): bool
    {
        $env = getenv('LOXCAN_REPORTER_GITHUB');

        return \is_string($env) && $env !== '';
    }
}
