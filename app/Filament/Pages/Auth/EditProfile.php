<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

/**
 * Profile screen for the Control Center.
 *
 * Filament's stock page edits name, email and password. This app signs in with
 * a username and stores no email, so the email field is replaced with the
 * username — otherwise there is no way to change either credential from the UI.
 */
class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form->schema([
            $this->getNameFormComponent(),

            TextInput::make('username')
                ->label(__('wassili.username'))
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->helperText(__('wassili.username_help')),

            // Leaving these blank keeps the current password.
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }
}
