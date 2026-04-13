<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BaseResource;
use App\Helpers\AccountingHelper;
use App\Models\Account;
use App\Models\Currency;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Illuminate\Database\Query\Builder;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Support\Icons\Heroicon;
use PhpParser\Node\Stmt\Label;
use UnitEnum;

use App\Services\Accounting\BalanceService;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Actions;

use App\Models\Views\AccountStatementSummaryView;
use App\Models\Views\AccountStatementReportView;
use Filament\Forms\Components\Checkbox;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class AccountStatementDashboard extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?string $title = 'كشف الحساب';
    protected static string|UnitEnum|null $navigationGroup = 'التقارير';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.account-statement-dashboard';

    public ?int $accountId = null;
    public ?int $currencyId = null;
    public ?string $date_type = 'until_today';
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public bool $is_summary_only = false;
    public bool $isReportGenerated = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function updated($property): void
    {
        // إذا تغير أي فلتر، نقوم بإخفاء النتائج حتى يضغط المستخدم على "عرض" مرة أخرى
        if (in_array($property, ['accountId', 'currencyId', 'date_type', 'fromDate', 'toDate', 'is_summary_only'])) {
            $this->isReportGenerated = false;
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('accountId')
                    ->label('الحساب')
                    ->options(fn() => ['' => 'كافة الحسابات'] + BaseResource::getChildrenAccountOptions())
                    ->searchable()
                    ->placeholder('كافة الحسابات')
                    ->reactive()
                    ->afterStateUpdated(function ($set, $state) {
                        if (empty($state)) {
                            $set('date_type', 'until_today');
                        }
                    }),

                Select::make('currencyId')
                    ->label('العملة')
                    ->options(fn() => BaseResource::getCurrencyOptions())
                    ->searchable()
                    ->placeholder('كافة العملات'),

                Select::make('date_type')
                    ->label('نوع التاريخ')
                    ->options([
                        'daily' => 'خلال يوم',
                        'period' => 'خلال فترة',
                        'monthly' => 'خلال شهر',
                        'yearly' => 'خلال سنة',
                        'until_today' => 'حتى يوم',
                    ])
                    ->native(false)
                    ->searchable()
                    ->reactive()
                    ->disabled(fn($get) => empty($get('accountId')))
                    ->default('until_today'),

                DatePicker::make('fromDate')
                    ->label('من تاريخ')
                    ->visible(fn($get) => $get('date_type') === 'period'),

                DatePicker::make('toDate')
                    ->label(fn($get) => match ($get('date_type')) {
                        'daily' => 'التاريخ',
                        'monthly' => 'تاريخ ضمن الشهر',
                        'yearly' => 'تاريخ ضمن السنة',
                        default => 'التاريخ',
                    })
                    ->default(now())
                    ->visible(fn($get) => !in_array($get('date_type'), ['any_date', null])),

                Checkbox::make('is_summary_only')
                    ->label('عرض كشف اجمالي فقط')
                    ->visible(fn($get) => !empty($get('accountId')))

            ])
            ->columns(3);
    }

    public function search(): void
    {
        $this->isReportGenerated = true;
    }

    public function table(Table $table): Table
    {
        $isSummary = empty($this->accountId) || $this->is_summary_only;

        return $table
            ->query(fn() => $this->getTableQuery())

            ->columns([
                TextColumn::make('اسم الحساب')
                    ->label('اسم الحساب')
                    ->searchable()
                    ->sortable()
                    ->visible($isSummary),

                TextColumn::make('مدين')
                    ->label('مدين')
                    ->numeric()
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->summarize(AccountingHelper::moneySummarizer(null)),

                TextColumn::make('دائن')
                    ->label('دائن')
                    ->numeric()
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->summarize(AccountingHelper::moneySummarizer(null)),

                TextColumn::make('العملة')
                    ->label('العملة')
                    ->badge()
                    ->color('gray'),



                // أعمـدة خاصـة بكافـة الحسابـات
                TextColumn::make('المقابل مدين')
                    ->label('المقابل مدين')
                    ->numeric()
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->visible($isSummary)
                    ->summarize(AccountingHelper::moneySummarizer('')),

                TextColumn::make('المقابل دائن')
                    ->label('المقابل دائن')
                    ->numeric()
                    ->formatStateUsing(fn($state) => AccountingHelper::formatMoney($state))
                    ->visible($isSummary)
                    ->summarize(AccountingHelper::moneySummarizer('')),

                // أعمـدة التـفاصـيل
                TextColumn::make('التاريخ')
                    ->label('التاريخ')
                    ->date('Y/m/d')
                    ->visible(! $isSummary),

                TextColumn::make('نوع السند')
                    ->label('نوع السند')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'سند قبض عملاء' => 'success',
                        'سند صرف عملاء' => 'danger',
                        'سند قيد بسيط' => 'info',
                        default => 'warning',
                    })
                    ->visible(! $isSummary),

                TextColumn::make('الرقم')
                    ->label('الرقم')
                    ->visible(! $isSummary),

                TextColumn::make('رقم المرجع')
                    ->label('رقم المرجع')
                    ->placeholder('-')
                    ->visible(! $isSummary),

                TextColumn::make('البيان')
                    ->label('البيان')
                    ->wrap()
                    ->limit(50)
                    ->visible(! $isSummary),
                 


            ])
            ->groups([
                Group::make('العملة')
                    ->label('العملة')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultSort(
                empty($this->accountId)
                    ? 'اسم الحساب'
                    : (empty($this->currencyId) ? 'العملة' : 'التاريخ'),
                'desc'
            )
            ->recordAction(null)
            ->recordUrl(null)
            ->paginated([10, 25, 50, 100])
            ->striped();
    }

    protected function getTableQuery(): EloquentBuilder
    {
        if (! $this->isReportGenerated) {
            // نعود فوراً ونوقف أي استعلام أو منطق إضافي لتوفير موارد السيرفر
            return AccountStatementSummaryView::query()->whereRaw('1=0');
        }

        if (empty($this->accountId) || $this->is_summary_only) {
            $query = AccountStatementSummaryView::query()
                ->select([
                    '*',
                    DB::raw('CASE WHEN `المبلغ المكافئ` < 0 THEN ABS(`المبلغ المكافئ`) ELSE 0 END AS `المقابل مدين`'),
                    DB::raw('CASE WHEN `المبلغ المكافئ` > 0 THEN `المبلغ المكافئ` ELSE 0 END AS `المقابل دائن`'),
                ]);
        } else {
            $query = AccountStatementReportView::query()
                ->select([
                    '*',
                    DB::raw('0 AS `المقابل مدين`'),
                    DB::raw('0 AS `المقابل دائن`'),
                ]);
        }

        // تطبيق الفلاتر باستخدام الـ Helper المركزي
        $query = AccountingHelper::applyStatementFilters($query, [
            'accountId' => $this->accountId,
            'currencyId' => $this->currencyId,
            'date_type' => $this->date_type,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'is_summary_only' => $this->is_summary_only,
        ]);

        return $query;
    }
}
