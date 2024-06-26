<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column(type: Types::INTEGER), ORM\GeneratedValue]
    private ?int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private string $slug;

    #[ORM\Column(type: Types::TEXT)]
    private string $question;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private \DateTimeInterface $askedAt;

    #[ORM\Column(type: Types::INTEGER)]
    private int $votes = 0;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class, fetch: "EXTRA_LAZY")]
    #[ORM\OrderBy(["createdAt" => "DESC"])]
    private PersistentCollection $answers;

    #[ORM\OneToMany( mappedBy: "question", targetEntity: QuestionTag::class)]
    private PersistentCollection $questionTags;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->questionTags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAskedAt(): ?\DateTimeInterface
    {
        return $this->askedAt;
    }

    public function setAskedAt(?\DateTimeInterface $askedAt): self
    {
        $this->askedAt = $askedAt;

        return $this;
    }

    public function getVotes(): int
    {
        return $this->votes;
    }

    public function getVotesString(): string
    {
        $prefix = $this->getVotes() >=0 ? '+' : '-';

        return sprintf('%s %d', $prefix, abs($this->getVotes()));
    }

    public function setVotes(int $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    public function upVote(): self
    {
        $this->votes++;

        return $this;
    }

    public function downVote(): self
    {
        $this->votes--;

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function getApprovedAnswers(): Collection
    {
        return $this->answers->matching(AnswerRepository::createApprovedCriteria());
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|QuestionTag[]
     */
    public function getQuestionTags(): Collection
    {
        return $this->questionTags;
    }

    public function addQuestionTag(QuestionTag $questionTag): self
    {
        if (!$this->questionTags->contains($questionTag)) {
            $this->questionTags[] = $questionTag;
            $questionTag->setQuestion($this);
        }

        return $this;
    }

    public function removeQuestionTag(QuestionTag $questionTag): self
    {
        if ($this->questionTags->removeElement($questionTag)) {
            // set the owning side to null (unless already changed)
            if ($questionTag->getQuestion() === $this) {
                $questionTag->setQuestion(null);
            }
        }

        return $this;
    }
}
