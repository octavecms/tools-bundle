<?php

namespace VideInfra\ToolsBundle\Traits;

use VideInfra\ToolsBundle\Service\FormSerializer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JsonResponseTrait
 * @author Igor Lukashov <igor.lukashov@videinfra.com>
 */
trait JsonResponseTrait
{
    /**
     * @param FormInterface|null $form
     * @param bool $customStatus
     * @param array $customError
     * @return JsonResponse
     */
    protected function generateResponse(FormInterface $form = null, bool $customStatus = true, array $customError = [])
    {
        /** @var FormSerializer $serializer */
        $serializer = $this->get('vig.tools.form.serializer');
        $response = new JsonResponse();

        $data = $serializer->serialize($form, $customStatus, $customError);
        return $response->setData($data);
    }
}