<?php

namespace Octave\ToolsBundle\Traits;

use Octave\ToolsBundle\Service\FormSerializer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JsonResponseTrait
 * @author Igor Lukashov <igor.lukashov@octavecms.com>
 */
trait JsonResponseTrait
{
    /**
     * @param FormInterface|null $form
     * @param bool $customStatus
     * @param array $customError
     * @param array $customData
     * @return JsonResponse
     */
    protected function generateResponse(FormInterface $form = null, bool $customStatus = true, array $customError = [],
                                        array $customData = [])
    {
        /** @var FormSerializer $serializer */
        $serializer = $this->get('octave.tools.form.serializer');
        $response = new JsonResponse();

        $data = $serializer->serialize($form, $customStatus, $customError);
        $data = array_merge($data, $customData);

        return $response->setData($data);
    }
}