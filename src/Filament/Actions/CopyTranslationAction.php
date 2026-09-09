<?php

namespace Wotz\FilamentBrigadaCms\Filament\Actions;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Livewire\Component;
use Wotz\OnlineScope\Models\Traits\HasOnlineScope;
use Wotz\TranslatableTabs\Actions\CopyTranslationAction as BaseCopyTranslationAction;

class CopyTranslationAction extends BaseCopyTranslationAction
{
    protected function setUp(): void
    {
        parent::setUp();

        // A utility next to the save actions, so it should not read as a primary button.
        $this->color('gray');

        $this->schema(fn (): array => array_filter([
            Select::make('from_locale')
                ->options($this->getLocales())
                ->required(),

            Select::make('to_locale')
                ->options($this->getLocales())
                ->required()
                ->different('from_locale'),

            $this->hasOnlineField()
                ? Checkbox::make('set_online')
                    ->label(__('Set target language online'))
                    ->default(false)
                : null,
        ]));

        $this->action(function (array $data, Component $livewire) {
            try {
                $livewire->data[$data['to_locale']] = $livewire->data[$data['from_locale']];

                if ($this->hasOnlineField() && isset($data['set_online'])) {
                    $livewire->data[$data['to_locale']]['online'] = $data['set_online'];
                }

                $this->success();
            } catch (\Throwable $e) {
                $this->failure();
            }
        });
    }

    protected function hasOnlineField(): bool
    {
        $record = $this->getRecord();

        if ($record) {
            return in_array(HasOnlineScope::class, class_uses_recursive($record));
        }

        $livewire = $this->getLivewire();

        if (method_exists($livewire, 'getResource')) {
            $modelClass = $livewire::getResource()::getModel();

            return in_array(HasOnlineScope::class, class_uses_recursive($modelClass));
        }

        return false;
    }
}
