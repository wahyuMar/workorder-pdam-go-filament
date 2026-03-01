<?php

namespace App\Enums;

enum BudgetItemCategory: string
{
    case PekerjaanPipaDinas = 'pekerjaan_pipa_dinas';
    case PekerjaanPipaInstalasi = 'pekerjaan_pipa_instalasi';

    public function getLabel(): string
    {
        return match ($this) {
            self::PekerjaanPipaDinas    => 'Pekerjaan Pipa Dinas',
            self::PekerjaanPipaInstalasi => 'Pekerjaan Pipa Instalasi',
        };
    }

    /** @return BudgetItemSubCategory[] */
    public function getSubCategories(): array
    {
        return array_filter(
            BudgetItemSubCategory::cases(),
            fn(BudgetItemSubCategory $sub) => $sub->getCategory() === $this,
        );
    }

    /** Returns value=>label map for use in Filament Select options */
    public function subCategoryOptions(): array
    {
        return collect($this->getSubCategories())
            ->mapWithKeys(fn(BudgetItemSubCategory $sub) => [
                $sub->value => $sub->getLabel(),
            ])
            ->all();
    }

    /** Build options array for all categories: value => label */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $c) => [$c->value => $c->getLabel()])
            ->all();
    }
}
