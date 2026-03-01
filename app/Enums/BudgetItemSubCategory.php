<?php

namespace App\Enums;

enum BudgetItemSubCategory: string
{
    case PekerjaanTanahDinas              = 'pekerjaan_tanah_dinas';
    case MaterialPipaDanAccDinas          = 'material_pipa_dan_acc_dinas';
    case JasaPasangPipaDanAccDinas        = 'jasa_pasang_pipa_dan_acc_dinas';
    case LainLainDinas                    = 'lain_lain_dinas';
    case PekerjaanTanahInstalasi          = 'pekerjaan_tanah_instalasi';
    case MaterialPipaDanAccInstalasi      = 'material_pipa_dan_acc_instalasi';
    case JasaPasangPipaDanAccInstalasi    = 'jasa_pasang_pipa_dan_acc_instalasi';
    case LainLainInstalasi                = 'lain_lain_instalasi';

    public function getLabel(): string
    {
        return match ($this) {
            self::PekerjaanTanahDinas           => 'Pekerjaan Tanah Dinas',
            self::MaterialPipaDanAccDinas        => 'Material Pipa & Acc Dinas',
            self::JasaPasangPipaDanAccDinas      => 'Jasa Pasang Pipa & Acc Dinas',
            self::LainLainDinas                  => 'Lain-lain Dinas',
            self::PekerjaanTanahInstalasi        => 'Pekerjaan Tanah Instalasi',
            self::MaterialPipaDanAccInstalasi    => 'Material Pipa & Acc Instalasi',
            self::JasaPasangPipaDanAccInstalasi  => 'Jasa Pasang Pipa & Acc Instalasi',
            self::LainLainInstalasi              => 'Lain-lain Instalasi',
        };
    }

    /** Returns the parent BudgetItemCategory this sub-category belongs to */
    public function getCategory(): BudgetItemCategory
    {
        return match ($this) {
            self::PekerjaanTanahDinas,
            self::MaterialPipaDanAccDinas,
            self::JasaPasangPipaDanAccDinas,
            self::LainLainDinas          => BudgetItemCategory::PekerjaanPipaDinas,

            self::PekerjaanTanahInstalasi,
            self::MaterialPipaDanAccInstalasi,
            self::JasaPasangPipaDanAccInstalasi,
            self::LainLainInstalasi      => BudgetItemCategory::PekerjaanPipaInstalasi,
        };
    }

    /** Returns value=>label options filtered by a given category value string */
    public static function forCategory(string $categoryValue): array
    {
        $category = BudgetItemCategory::from($categoryValue);

        return $category->subCategoryOptions();
    }

    /** Returns all value=>label options (unfiltered) */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $c) => [$c->value => $c->getLabel()])
            ->all();
    }

    /**
     * Returns BudgetItemSubCategory cases that belong to the given category.
     *
     * @return self[]
     */
    public static function getSubCategoriesByCategory(BudgetItemCategory $category): array
    {
        return array_values(array_filter(
            self::cases(),
            fn(self $sub) => $sub->getCategory() === $category,
        ));
    }
}
