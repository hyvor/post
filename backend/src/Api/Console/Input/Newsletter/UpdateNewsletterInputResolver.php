<?php

namespace App\Api\Console\Input\Newsletter;

use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateNewsletterInputResolver implements ValueResolverInterface
{

    public function __construct(
        private ValidatorInterface $validator
    ) {
    }

    /**
     * @return iterable<UpdateNewsletterInput>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        // check attribute MapRequestPayload
        $attribute = $argument->getAttributesOfType(MapRequestPayload::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;
        if (!$attribute) {
            return [];
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return [];
        }

        $input = new UpdateNewsletterInput();

        foreach ($data as $key => $value) {
            if (property_exists($input, $key)) {
                $input->set($key, $value);
            }
        }

        $errors = $this->validator->validate($input);
        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException(
                previous: new ValidatorException($errors)
            );
        }

        return [$input];
    }

}
