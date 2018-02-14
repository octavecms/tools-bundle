<?php

namespace VideInfra\ToolsBundle\Service;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

/**
 * Class FormSerializer
 * @author Igor Lukashov <igor.lukashov@videinfra.com>
 */
class FormSerializer
{
    /**
     * @param Form|null $form
     * @param bool $customStatus
     * @param array $customError
     * @return array
     */
    public function serialize(Form $form = null, bool $customStatus = true, array $customError = [])
    {
        $result = [
            'status' => $customStatus,
            'errors' => $customError,
        ];

        if ($form) {
            foreach ($form->getErrors(true) as $key => $error) {

                $name = $error->getOrigin()->getConfig()->getName();

                if ($error->getOrigin()->getParent() &&
                    $error->getOrigin()->getParent()->getConfig()->getType()->getInnerType() instanceof RepeatedType) {
                    $name = $error->getOrigin()->getParent()->getName() . '[' . $name . ']';
                }

                /** @var FormError $error */
                $result['errors'][] = [
                    'id' => $name,
                    'message' => $error->getMessage(),
                ];
            }

            if (!$form->isValid()) {
                $result['status'] = false;
            }
        }

        return $result;
    }
}