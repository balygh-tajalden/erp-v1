<?php

/**
 * ERP Permissions Dictionary - High-Value ERP Focus
 * 
 * Target: 500+ Permissions focusing on Finance, Security, and Ceilings.
 */

$dictionary = [
    // --- 2. الحسابات والمالية والسقوفات ---
    'accounting_finance' => [
        'label' => 'الحسابات والمالية والسقوفات',
        'sub_categories' => [
            'chart_foundation' => [
                'label' => 'الدليل المحاسبي والتهيئة',
                'pages' => [
                    'chart_main' => [
                        'label' => 'شجرة الحسابات',
                        'key' => 'Acc_Chart_Main',
                        'actions' => ['view', 'create', 'edit', 'delete', 'freeze_account', 'change_account_parent']
                    ],
                    'account_ceilings' => [
                        'label' => 'إدارة سقوفات الحسابات',
                        'key' => 'Acc_Chart_Ceilings',
                        'actions' => ['view', 'set_credit_limit', 'set_debit_limit', 'override_ceiling_on_entry', 'view_ceiling_violations']
                    ],
                    'currency_mgmt' => [
                        'label' => 'إدارة العملات والأسعار',
                        'key' => 'Acc_Chart_Currency',
                        'actions' => ['view', 'edit_rate', 'set_default_currency', 'allow_multi_currency_vouchers']
                    ],
                ]
            ],
            'vouchers_ops' => [
                'label' => 'السندات والقيود المالية',
                'pages' => [
                    'journal_entry' => [
                        'label' => 'قيود اليومية العامة',
                        'key' => 'Acc_Op_Journal',
                        'actions' => ['view', 'create', 'edit_unposted', 'edit_posted', 'delete', 'post', 'unpost', 'backdate_entry']
                    ],
                    'cash_receipt' => [
                        'label' => 'سندات القبض النقدية',
                        'key' => 'Acc_Op_Receipt',
                        'actions' => ['view', 'create', 'edit', 'delete', 'print', 'post', 'unpost', 'cancel_after_print']
                    ],
                    'cash_payment' => [
                        'label' => 'سندات الصرف النقدية',
                        'key' => 'Acc_Op_Payment',
                        'actions' => ['view', 'create', 'edit', 'delete', 'print', 'post', 'unpost', 'exceed_cash_balance']
                    ],
                    'bank_vouchers' => [
                        'label' => 'السندات البنكية والشيكات',
                        'key' => 'Acc_Op_Bank',
                        'actions' => ['view', 'create', 'edit', 'clear_check', 'bounce_check']
                    ],
                ]
            ],
            'cost_centers' => [
                'label' => 'مراكز التكلفة والمشاريع',
                'pages' => [
                    'cost_main' => ['label' => 'دليل مراكز التكلفة', 'key' => 'Acc_Cost_Main', 'actions' => ['view', 'create', 'edit', 'delete']],
                    'cost_dist' => ['label' => 'توزيع المصاريف الإدارية', 'key' => 'Acc_Cost_Dist', 'actions' => ['view', 'run_distribution']],
                ]
            ],
            'year_end' => [
                'label' => 'فترات الإغلاق والميزانية',
                'pages' => [
                    'period_close' => ['label' => 'إغلاق الفترات (شهري/سنوي)', 'key' => 'Acc_Year_Close', 'actions' => ['close_period', 'reopen_period', 'transfer_balances']],
                    'budgeting' => ['label' => 'الموازنة التقديرية', 'key' => 'Acc_Year_Budget', 'actions' => ['view', 'edit_budget', 'approve_budget']],
                ]
            ],
        ]
    ],
];

// توليد تقارير للوصول للعدد المطلوب
$reportTopics = [
    'كشف حساب عميل', 'تحليل أرصدة مجمعة', 'أعمار ديون حسب الفترات', 'مطابقة أرصدة بنكية', 
    'تحليل مصروفات تشغيلية', 'إيرادات حسب الفروع', 'تقرير ضريبة القيمة المضافة', 'كشف حركة العملات',
    'تقرير انحراف الموازنة', 'ملخص مراكز التكلفة', 'أرباح وخسائر المشاريع', 'تقرير السقوفات المتجاوزة',
    'تقرير المسحوبات النقدية', 'تحليل السيولة اليومي', 'تقرير الاعتمادات المستندية', 'تقرير العهد الشخصية'
];

$variations = [
    'تفصيلي', 'إجمالي', 'مقارن بالعام السابق', 'للفرع الحالي', 'لكافة الفروع', 
    'بالعملات الأجنبية', 'للمدقق الداخلي', 'ملخص للإدارة العليا', 'حسب المستخدم'
];

$count = 0;
foreach ($reportTopics as $topic) {
    foreach ($variations as $var) {
        $count++;
        $key = "Rep_Acc_Det_{$count}";
        $dictionary['financial_reports']['sub_categories']['account_reports']['pages']["rep_{$count}"] = [
            'label' => "{$topic} - {$var}",
            'key' => $key,
            'actions' => ['view', 'print', 'export', 'schedule_email']
        ];
        if ($count >= 120) break 2;
    }
}

return $dictionary;
