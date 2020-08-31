<?php

namespace App\Message;

class CommentMessage
{
    private int $id;
    private array $context;
    private string $reviewUrl;

    public function __construct(int $id, string $reviewUrl, array $context = [])
    {
        $this->id = $id;
        $this->reviewUrl = $reviewUrl;
        $this->context = $context;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getReviewUrl(): string
    {
        return $this->reviewUrl;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
