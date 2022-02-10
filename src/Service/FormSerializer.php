<?php

namespace Octave\ToolsBundle\Service;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormSerializer
 * @author Igor Lukashov <igor.lukashov@octavecms.com>
 */
class FormSerializer
{
    /**
     * @param FormInterface|null $form
     * @param bool $customStatus
     * @param array $customError
     * @return array
     */
    public function serialize(FormInterface $form = null, bool $customStatus = true, array $customError = [])
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
                    $name = $error->getOrigin()->getParent()->getParent()->getName() . '[' . $error->getOrigin()->getParent()->getName() . ']'.'[' . $name . ']';
                }

                /** @var FormError $error */
                $result['errors'][] = [
                    'id' => $name,
                    'message' => $error->getMessage(),
                ];
            }

            if ($form->isSubmitted() && !$form->isValid()) {
                $result['status'] = false;
            }
        }

        return $result;
    }
}