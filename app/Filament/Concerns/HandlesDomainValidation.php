<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

trait HandlesDomainValidation
{
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (ValidationException $exception) {
            $this->showDomainErrors($exception);
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (ValidationException $exception) {
            $this->showDomainErrors($exception);
        }
    }

    private function showDomainErrors(ValidationException $exception): never
    {
        $messages = [];
        foreach ($exception->errors() as $field => $errors) {
            $messages[$this->form->getStatePath().'.'.$field] = $errors;
        }
        throw ValidationException::withMessages($messages);
    }
}
