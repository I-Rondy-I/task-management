<?php
namespace App\Serializer;

use App\Entity\Task;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class TaskSerializer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    public function normalize($object, string $format = null, array $context = []): array
    {
        if (!$object instanceof Task) {
            return [];
        }

        $data = [
            'id' => $object->getId()->toString(),
            'title' => $object->getTitle(),
            'description' => $object->getDescription(),
            'deadline' => $object->getDeadline()->format('Y-m-d\TH:i:s'),
            'status' => $object->getStatus()->value,
            'parentTaskId' => $object->getParentTask() ? $object->getParentTask()->getId()->toString() : null,
            'subtasks' => [],
        ];

        foreach ($object->getSubtasks() as $subtask) {
            $data['subtasks'][] = $this->normalize($subtask, $format, $context);
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Task;
    }
}