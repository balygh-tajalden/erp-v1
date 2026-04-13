<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Stage 11: System Views - Standardized for Linux Case Sensitivity
        
        // 1. vw_accounts
        DB::statement("DROP VIEW IF EXISTS vw_accounts");
        DB::statement("CREATE VIEW vw_accounts AS
            SELECT 
                a.ID,
                a1.AccountName AS `الحساب الأب`,
                a.FatherNumber AS `رقم الحساب الاب`,
                a.AccountName AS `اسم الحساب`,
                a.AccountNumber AS `رقم الحساب`,
                CASE a.AccountTypeID
                    WHEN 1 THEN 'رئيسي'
                    WHEN 2 THEN 'فرعي'
                END AS `نوع الحساب`,
                CASE a.AccountReference
                    WHEN 1 THEN 'الميزانية العمومية'
                    WHEN 2 THEN 'حساب الأرباح والخسائر'
                END AS `الحساب الختامي`,
                CASE 
                    WHEN a.IsCustomer = 1 AND a.IsSupplier = 1 THEN 'عميل ومورد'
                    WHEN a.IsCustomer = 1 THEN 'عميل'
                    WHEN a.IsSupplier = 1 THEN 'مورد'
                    ELSE '' 
                END AS `النوع`,
                u.UserName AS `المستخدم`,
                b.BranchName AS `الفرع`,
                a.CreatedDate AS `وقت الإدخال`,
                a.AccountCode AS `الرقم المميز`
            FROM tblAccounts a
            LEFT JOIN tblAccounts a1 ON a.FatherNumber = a1.AccountNumber
            LEFT JOIN tblUsers u ON a.CreatedBy = u.ID
            LEFT JOIN tblBranches b ON a.BranchID = b.ID");

        // 2. vw_accountstatement
        DB::statement("DROP VIEW IF EXISTS vw_accountstatement");
        DB::statement("CREATE VIEW vw_accountstatement AS
            SELECT
                e.ID AS EntryID,
                e.RecordID,
                ed.AccountID,
                ed.CurrencyID,
                ed.Amount,
                ed.MCAmount,
                e.RecordNumber AS `الرقم`,
                e.TheDate AS `التاريخ`,
                d.DocumentName AS `نوع السند`,
                a.AccountName AS `اسم الحساب`,
                a.AccountNumber AS `رقم الحساب`,
                IFNULL(cf.AccountName, '') AS `الحساب الأب`,
                ed.Notes AS `البيان`, 
                c.CurrencyName AS `العملة`,
                CASE WHEN ed.Amount < 0 THEN ABS(ed.Amount) ELSE 0 END AS `مدين`,
                CASE WHEN ed.Amount > 0 THEN ed.Amount ELSE 0 END AS `دائن`,
                ed.Amount AS `المبلغ`,
                ed.MCAmount AS `المبلغ المكافئ`,
                DATE(e.CreatedDate) AS `تاريخ القيد`,
                u.UserName AS `المستخدم`,
                b.BranchName AS `اسم الفرع`,
                e.BranchID
            FROM tblEntries e
            JOIN tblEntryDetails ed ON ed.ParentID = e.ID
            JOIN tblAccounts a ON a.ID = ed.AccountID
            LEFT JOIN tblAccounts cf ON cf.AccountNumber = a.FatherNumber
            JOIN tblCurrencies c ON c.ID = ed.CurrencyID
            JOIN tblDocumentTypes d ON d.ID = e.DocumentID
            JOIN tblUsers u ON u.ID = e.CreatedBy
            JOIN tblBranches b ON b.ID = e.BranchID
            WHERE e.IsPosted = 1
            AND e.isDeleted = 0
            AND ed.deleted_at IS NULL");

        // 3. vw_accountstatementreport
        DB::statement("DROP VIEW IF EXISTS vw_accountstatementreport");
        DB::statement("CREATE VIEW vw_accountstatementreport AS
            SELECT
                ed.ID AS id,
                e.ID AS EntryID,
                ed.AccountID,
                ed.CurrencyID,
                e.RecordNumber AS `الرقم`,
                d.DocumentName AS `نوع السند`,
                CASE WHEN ed.Amount < 0 THEN ABS(ed.Amount) ELSE 0 END AS `مدين`,
                CASE WHEN ed.Amount > 0 THEN ed.Amount ELSE 0 END AS `دائن`,
                ed.Amount AS `المبلغ`,
                c.CurrencyName AS `العملة`,
                ed.Notes AS `البيان`,
                a.AccountName AS `اسم الحساب`,
                CAST(e.TheDate AS DATE) AS `التاريخ`,
                CASE 
                    WHEN d.DocumentName = 'سند قبض عملاء' THEN cr.ReferenceNumber
                    WHEN d.DocumentName = 'سند صرف عملاء' THEN cp.ReferenceNumber
                    WHEN d.DocumentName = 'سند قيد بسيط' THEN se.ReferenceNumber
                    ELSE NULL
                END AS `رقم المرجع`
            FROM tblEntries e
            JOIN tblEntryDetails ed ON e.ID = ed.ParentID
            JOIN tblAccounts a ON a.ID = ed.AccountID
            JOIN tblCurrencies c ON c.ID = ed.CurrencyID
            JOIN tblDocumentTypes d ON d.ID = e.DocumentID
            LEFT JOIN tblcustrecv cr ON cr.EntryID = e.ID AND d.DocumentName = 'سند قبض عملاء'
            LEFT JOIN tblcustpay cp ON cp.EntryID = e.ID AND d.DocumentName = 'سند صرف عملاء'
            LEFT JOIN tblSimpleEntries se ON se.EntryID = e.ID AND d.DocumentName = 'سند قيد بسيط'
            WHERE e.IsPosted = 1 AND e.isDeleted = 0 AND ed.deleted_at IS NULL");

        // 4. vw_accountstatementsummary
        DB::statement("DROP VIEW IF EXISTS vw_accountstatementsummary");
        DB::statement("CREATE VIEW vw_accountstatementsummary AS
            SELECT
                MIN(ed.ID) AS id,
                ed.AccountID,
                a.AccountName AS `اسم الحساب`,
                ed.CurrencyID,
                c.CurrencyName AS `العملة`,
                CASE WHEN SUM(ed.Amount) < 0 THEN ABS(SUM(ed.Amount)) ELSE 0 END AS `مدين`,
                CASE WHEN SUM(ed.Amount) > 0 THEN SUM(ed.Amount) ELSE 0 END AS `دائن`,
                SUM(ed.Amount) AS `صافي المبلغ`,
                SUM(ed.MCAmount) AS `المبلغ المكافئ`,
                e.BranchID,
                MIN(e.TheDate) AS `أول تاريخ`,
                MAX(e.TheDate) AS `آخر تاريخ`
            FROM tblEntries e
            JOIN tblEntryDetails ed ON ed.ParentID = e.ID
            JOIN tblAccounts a ON a.ID = ed.AccountID
            JOIN tblCurrencies c ON c.ID = ed.CurrencyID
            WHERE e.IsPosted = 1 
              AND e.IsDeleted = 0
              AND ed.deleted_at IS NULL
              AND ed.Amount != 0
            GROUP BY ed.AccountID, a.AccountName, ed.CurrencyID, c.CurrencyName, e.BranchID
            HAVING SUM(ABS(ed.Amount)) > 0");

        // 5. vw_currencies
        DB::statement("DROP VIEW IF EXISTS vw_currencies");
        DB::statement("CREATE VIEW vw_currencies AS
            SELECT 
                TheNumber AS `الرقم`,
                CurrencyName AS `اسم العملة`,
                ArabicCode AS `الرمز العربي`,
                EnglishCode AS `الرمز الإنجليزي`,
                ISO_Code AS `كود ISO`,
                IsDefault AS `افتراضية`,
                CreatedDate AS `تاريخ الإضافة`,
                ModifiedDate AS `تاريخ التعديل`,
                BranchID AS `الفرع`
            FROM tblCurrencies");

        // 6. vw_currencyprices
        DB::statement("DROP VIEW IF EXISTS vw_currencyprices");
        DB::statement("CREATE VIEW vw_currencyprices AS
            SELECT 
                CP.ID,
                CP.SourceCurrencyID,
                CP.TargetCurrencyID,
                C1.CurrencyName AS `من عملة`,
                C2.CurrencyName AS `إلى عملة`,
                CP.ExchangePrice AS `سعر التحويل`,
                CP.BuyPrice AS `سعر الشراء`,
                CP.MinBuyPrice AS `أقل سعر شراء`,
                CP.MaxBuyPrice AS `أعلى سعر شراء`,
                CP.SellPrice AS `سعر البيع`,
                CP.MinSellPrice AS `أقل سعر بيع`,
                CP.MaxSellPrice AS `أعلى سعر بيع`,
                CP.Notes AS `ملاحظات`,
                U.UserName AS `المستخدم`,
                B.BranchName AS `الفرع`,
                CP.CreatedDate AS `تاريخ الإدخال`
            FROM tblCurrenciesPrices CP
            INNER JOIN tblCurrencies C1 ON CP.SourceCurrencyID = C1.ID
            INNER JOIN tblCurrencies C2 ON CP.TargetCurrencyID = C2.ID
            LEFT JOIN tblUsers U ON CP.CreatedBy = U.ID
            LEFT JOIN tblBranches B ON CP.BranchID = B.ID");

        // 7. vw_customerpayments
        DB::statement("DROP VIEW IF EXISTS vw_customerpayments");
        DB::statement("CREATE VIEW vw_customerpayments AS
            SELECT 
                p.RowVersion,
                p.ID,
                p.Thenumber AS `الرقم`,
                p.AccountID,
                p.FundAccountID,
                p.CurrencyID,
                p.BranchID,
                p.ExchangeAmount,
                p.Amount as `المبلغ`,
                cu.CurrencyName AS `العملة`,
                a.AccountName AS `الحساب`,
                f.AccountName AS `الصندوق`,
                p.ExchangeAmount AS `مبلغ الحساب`,
                p.ExchangeCurrencyID,
                excu.CurrencyName AS `عملة الحساب`,
                p.TheDate AS `التاريخ`,
                p.ReferenceNumber AS `رقم المرجع`,
                p.Notes AS `الملاحظات`,
                p.Handling AS `المناولة`,
                u.Username AS `المستخدم المدخل`,
                b.BranchName AS `اسم الفرع`,
                p.CreatedDate AS `تاريخ الإدخال`,
                p.EntryID
            FROM tblcustpay p 
            LEFT JOIN tblAccounts a ON p.AccountID = a.ID
            LEFT JOIN tblAccounts f ON p.FundAccountID = f.ID
            LEFT JOIN tblCurrencies cu ON p.CurrencyID = cu.ID
            LEFT JOIN tblCurrencies excu ON p.ExchangeCurrencyID = excu.ID
            LEFT JOIN tblUsers u ON p.CreatedBy = u.ID
            LEFT JOIN tblBranches b ON p.BranchID = b.ID
            WHERE p.isDeleted = 0");

        // 8. vw_customerreceipts
        DB::statement("DROP VIEW IF EXISTS vw_customerreceipts");
        DB::statement("CREATE VIEW vw_customerreceipts AS
            SELECT   
                c.RowVersion,
                c.ID,
                c.Thenumber AS `الرقم`,
                c.AccountID,
                c.FundAccountID,
                c.CurrencyID,
                c.ExchangeAmount,
                c.BranchID,
                c.Amount as `المبلغ`,
                cu.CurrencyName AS `العملة`,
                a.AccountName AS `الحساب`,
                f.AccountName AS `الصندوق`,
                c.ExchangeAmount AS `مبلغ الحساب`,
                c.ExchangeCurrencyID,
                excu.CurrencyName AS `عملة الحساب`,
                c.TheDate AS `التاريخ`,
                c.ReferenceNumber AS `رقم المرجع`,
                c.Notes AS `الملاحظات`,
                c.Handling AS `المناولة`,
                u.Username AS `المستخدم المدخل`,
                b.BranchName AS `اسم الفرع`,
                c.CreatedDate AS `تاريخ الإدخال`,
                c.EntryID
            FROM tblcustrecv c 
            LEFT JOIN tblAccounts a ON c.AccountID = a.ID
            LEFT JOIN tblAccounts f ON c.FundAccountID = f.ID
            LEFT JOIN tblCurrencies cu ON c.CurrencyID = cu.ID
            LEFT JOIN tblCurrencies excu ON c.ExchangeCurrencyID = excu.ID
            LEFT JOIN tblUsers u ON c.CreatedBy = u.ID
            LEFT JOIN tblBranches b ON c.BranchID = b.ID
            WHERE c.isDeleted = 0");

        // 9. vw_historyview
        DB::statement("DROP VIEW IF EXISTS vw_historyview");
        DB::statement("CREATE VIEW vw_historyview AS
            SELECT 
                h.id ,
                d.DocumentName AS `المستند`, 
                h.RecordID AS `رقم_السند`, 
                u.Username AS `المستخدم`,
                o.OperationName AS `نوع_العملية`, 
                h.ChangeDate AS `تاريخ_العملية`,
                h.OldData AS `البيانات_السابقة`,
                h.NewData AS `البيانات_الجديدة`,
                h.Notes AS `الملاحظات`,
                h.MachineName AS `اسم_الجهاز`,
                h.OSUserName AS `مستخدم_النظام`
            FROM tblHistory h
            LEFT JOIN tblDocumentTypes d ON h.TableName = d.DocumentName
            LEFT JOIN tblUsers u ON h.ChangedBy = u.ID
            LEFT JOIN tbloperations o ON h.OperationID = o.ID");

        // 10. vw_simpleentries
        DB::statement("DROP VIEW IF EXISTS vw_simpleentries");
        DB::statement("CREATE VIEW vw_simpleentries AS
            SELECT 
                p.RowVersion,
                p.ID,
                p.Thenumber AS `الرقم`,
                p.BranchID ,
                p.Amount AS `المبلغ`,
                cu.CurrencyName AS `العملة`,
                a.AccountName AS `من حساب`,
                f.AccountName AS `الى حساب`,
                p.TheDate AS `التاريخ`,
                p.ReferenceNumber AS `رقم المرجع`,
                p.Notes AS `الملاحظات`,
                p.FromAccountID,
                p.ToAccountID,
                p.CurrencyID,
                u.Username AS `المستخدم`,
                b.BranchName AS `اسم الفرع`,
                p.CreatedDate AS `تاريخ الإدخال`,
                p.EntryID
            FROM tblSimpleEntries p
            LEFT JOIN tblAccounts a ON p.FromAccountID = a.ID
            LEFT JOIN tblAccounts f ON p.ToAccountID = f.ID
            LEFT JOIN tblCurrencies cu ON p.CurrencyID = cu.ID
            LEFT JOIN tblUsers u ON p.CreatedBy = u.ID
            LEFT JOIN tblBranches b ON p.BranchID = b.ID
            WHERE p.isDeleted = 0");

        // 11. vw_trialbalancehierarchical
        DB::statement("DROP VIEW IF EXISTS vw_trialbalancehierarchical");
        DB::statement("CREATE VIEW vw_trialbalancehierarchical AS
            WITH Opening AS (
                SELECT
                    a.ID AS AccountID,
                    a.AccountNumber,
                    a.AccountName,
                    a.FatherNumber,
                    a.AccountTypeID,
                    ed.CurrencyID,
                    SUM(ed.MCAmount) AS OpeningBalance
                FROM tblEntries e
                JOIN tblEntryDetails ed ON ed.ParentID = e.ID
                JOIN tblAccounts a ON a.ID = ed.AccountID
                WHERE e.isDeleted = 0 AND e.IsPosted = 1 AND ed.deleted_at IS NULL
                  AND e.TheDate < CURDATE()
                GROUP BY a.ID, a.AccountNumber, a.AccountName, a.FatherNumber, a.AccountTypeID, ed.CurrencyID
            ),
            Movement AS (
                SELECT
                    a.ID AS AccountID,
                    ed.CurrencyID,
                    SUM(ed.MCAmount) AS PeriodMovement
                FROM tblEntries e
                JOIN tblEntryDetails ed ON ed.ParentID = e.ID
                JOIN tblAccounts a ON a.ID = ed.AccountID
                WHERE e.IsDeleted = 0 AND e.IsPosted = 1 AND ed.deleted_at IS NULL
                  AND e.TheDate BETWEEN CURDATE() AND CURDATE()
                GROUP BY a.ID, ed.CurrencyID
            ),
            Combined AS (
                SELECT
                    o.AccountID,
                    o.AccountNumber,
                    o.AccountName,
                    o.FatherNumber,
                    o.AccountTypeID,
                    o.CurrencyID,
                    IFNULL(o.OpeningBalance, 0) AS OpeningBalance,
                    IFNULL(m.PeriodMovement, 0) AS PeriodMovement
                FROM Opening o
                LEFT JOIN Movement m ON o.AccountID = m.AccountID AND o.CurrencyID = m.CurrencyID
                UNION
                SELECT
                    m.AccountID,
                    NULL, NULL, NULL, NULL,
                    m.CurrencyID,
                    IFNULL(o.OpeningBalance, 0) AS OpeningBalance,
                    IFNULL(m.PeriodMovement, 0) AS PeriodMovement
                FROM Movement m
                LEFT JOIN Opening o ON o.AccountID = m.AccountID AND o.CurrencyID = m.CurrencyID
                WHERE o.AccountID IS NULL
            ),
            FinalData AS (
                SELECT
                    c.AccountNumber,
                    c.AccountName,
                    c.FatherNumber,
                    c.AccountTypeID,
                    c.CurrencyID,
                    c.OpeningBalance,
                    c.PeriodMovement,
                    c.OpeningBalance + c.PeriodMovement AS FinalBalance,
                    (LENGTH(c.AccountNumber) - LENGTH(REPLACE(c.AccountNumber, '.', ''))) AS Level
                FROM Combined c
            )
            SELECT * FROM FinalData");

        // 12. vw_usergroups
        DB::statement("DROP VIEW IF EXISTS vw_usergroups");
        DB::statement("CREATE VIEW vw_usergroups AS
            SELECT 
                ug.ID,
                ug.GroupNumber AS `رقم المجموعة`,
                ug.GroupName AS `اسم المجموعة`,
                ug.Description AS `الوصف`,
                b.BranchName AS `الفرع`,
                ug.CreatedDate AS `وقت الإدخال`,
                CASE WHEN ug.IsActive = 1 THEN 'فعالة' ELSE 'غير فعالة' END AS `الحالة`
            FROM tblUserGroups ug
            LEFT JOIN tblBranches b ON b.ID = ug.BranchID");

        // 13. vw_userlist
        DB::statement("DROP VIEW IF EXISTS vw_userlist");
        DB::statement("CREATE VIEW vw_userlist AS
            SELECT 
                u.ID ,
                u.UserName AS `اسم المستخدم`,
                g.GroupName  AS `مجموعة المستخدم`,
                b.BranchName AS `الفرع`,
                CASE WHEN u.IsActive = 1 THEN 'فعال' ELSE 'غير فعال' END AS `حالة المستخدم`,
                u.Notes AS `الملاحظات`,
                u.CreatedDate AS `تاريخ الإضافة`
            FROM tblUsers u
            LEFT JOIN tblUserGroups g ON u.UserGroupID = g.ID
            LEFT JOIN tblBranches b ON u.BranchID = b.ID");

        // 14. vwaccountlimits
        DB::statement("DROP VIEW IF EXISTS vwaccountlimits");
        DB::statement("CREATE VIEW vwaccountlimits AS
            SELECT 
                al.ID ,
                al.RowVersion ,
                al.GroupID ,
                CASE 
                    WHEN al.GroupID IS NOT NULL THEN 'مجموعة'
                    WHEN al.AccountID IS NOT NULL THEN 'حساب'
                END AS `نوع السقف`,
                g.GroupName as `اسم المجموعة`,
                a.AccountName as `اسم الحساب` ,
                al.Amount AS `المبلغ`,
                al.CurrencyID ,
                CASE 
                    WHEN al.CurrencyID = 0 THEN '<< كافة العملات >>'
                    ELSE c.CurrencyName 
                END AS `العملة`,
                al.Notes AS `ملاحظات`,
                al.IsActive AS `نشط`,
                CASE 
                    WHEN al.IsActive = 1 THEN 'فعال'
                    ELSE 'غير فعال'
                END AS `الحالة`,
                u1.UserName AS `المستخدم`,
                DATE_FORMAT(al.CreatedDate, '%Y-%m-%d %H:%i:%s') AS `تاريخ الإنشاء`,
                al.ModifiedBy ,
                u2.UserName AS `تم التعديل بواسطة`,
                DATE_FORMAT(al.ModifiedDate, '%Y-%m-%d %H:%i:%s') AS `تاريخ التعديل`,
                al.BranchID ,
                b.BranchName AS `الفرع`
            FROM tblAccountLimits al
            LEFT JOIN tblGroups g ON al.GroupID = g.ID
            LEFT JOIN tblAccounts a ON al.AccountID = a.ID
            LEFT JOIN tblCurrencies c ON al.CurrencyID = c.ID
            LEFT JOIN tblUsers u1 ON al.CreatedBy = u1.ID
            LEFT JOIN tblUsers u2 ON al.ModifiedBy = u2.ID
            LEFT JOIN tblBranches b ON al.BranchID = b.ID");

        // 15. vwbuycurrencies
        DB::statement("DROP VIEW IF EXISTS vwbuycurrencies");
        DB::statement("CREATE VIEW vwbuycurrencies AS
            SELECT 
                B.ID,
                B.RowVersion,
                B.Thenumber AS `الرقم`,
                A.AccountName AS `اسم الحساب`,
                B.Amount AS `المبلغ`,
                B.BranchID,
                C1.CurrencyName AS `العملة المشتراة`,
                B.Price AS `سعر الشراء`,
                B.ExchangeAmount AS `المبلغ المدفوع`,
                C2.CurrencyName AS `العملة المدفوعة`,
                F.AccountName AS `حساب الدفع`,
                B.CommissionAmount AS `العمولة`,
                C3.CurrencyName AS `عملة العمولة`,
                DATE_FORMAT(B.TheDate, '%Y-%m-%d') AS `التاريخ`,
                CASE B.PurchaseMethod 
                    WHEN 'نقد' THEN 'نقد'
                    WHEN 'حساب' THEN 'حساب'
                END AS `طريقة الشراء`,
                B.Notes AS `الملاحظات`,
                U.UserName AS `المستخدم`,
                BR.BranchName AS `الفرع`,
                DATE_FORMAT(B.CreatedDate, '%Y-%m-%d %H:%i:%s') AS `تاريخ الإدخال`,
                DATE_FORMAT(B.ModifiedDate, '%Y-%m-%d %H:%i:%s') AS `آخر تعديل`,
                B.EntryID as EntryID
            FROM tblBuyCurrencies B
            LEFT JOIN tblAccounts A ON B.AccountID = A.ID
            LEFT JOIN tblAccounts F ON B.FundAccountID = F.ID
            LEFT JOIN tblCurrencies C1 ON B.CurrencyID = C1.ID
            LEFT JOIN tblCurrencies C2 ON B.ExchangeCurrencyID = C2.ID
            LEFT JOIN tblCurrencies C3 ON B.CommissionCurrencyID = C3.ID
            LEFT JOIN tblUsers U ON B.CreatedBy = U.ID
            LEFT JOIN tblBranches BR ON B.BranchID = BR.ID
            WHERE B.isDeleted = 0");

        // 16. vwgroups
        DB::statement("DROP VIEW IF EXISTS vwgroups");
        DB::statement("CREATE VIEW vwgroups AS
            SELECT 
                g.ID,
                t.GroupTypeName AS `نوع المجموعة`,
                p.PurposeName AS `التصنيف`,
                g.GroupName AS `اسم المجموعة`,
                g.Notes AS `الوصف`,
                g.IsActive AS `فعالة`,
                u.UserName AS `المستخدم`,
                g.CreatedDate AS `تاريخ الإدخال`,
                g.ModifiedBy,
                g.ModifiedDate,
                g.PurposeID
            FROM tblGroups g
            JOIN tblgrouptypespurposes p ON g.PurposeID = p.ID
            JOIN tblGroupTypes t ON p.GroupTypeID = t.ID
            LEFT JOIN tblUsers u ON g.CreatedBy = u.ID");

        // 17. vwsellcurrencies
        DB::statement("DROP VIEW IF EXISTS vwsellcurrencies");
        DB::statement("CREATE VIEW vwsellcurrencies AS
            SELECT 
                S.ID,
                S.RowVersion,
                S.Thenumber AS `الرقم`,
                A.AccountName AS `اسم الحساب`,
                S.Amount AS `المبلغ`,
                S.BranchID,
                C1.CurrencyName AS `العملة المباعة`,
                S.Price AS `سعر البيع`,
                S.ExchangeAmount AS `المبلغ المستلم`,
                C2.CurrencyName AS `العملة المستلمة`,
                F.AccountName AS `حساب القيمة`,
                DATE_FORMAT(S.TheDate, '%Y-%m-%d') AS `التاريخ`,
                CASE S.PurchaseMethod 
                    WHEN 'نقد' THEN 'نقد'
                    WHEN 'حساب' THEN 'حساب'
                END AS `طريقة البيع`,
                S.Notes AS `الملاحظات`,
                U.UserName AS `المستخدم`,
                BR.BranchName AS `الفرع`,
                DATE_FORMAT(S.CreatedDate, '%Y-%m-%d %H:%i:%s') AS `تاريخ الإدخال`,
                DATE_FORMAT(S.ModifiedDate, '%Y-%m-%d %H:%i:%s') AS `آخر تعديل`,
                S.EntryID as EntryID
            FROM tblSellCurrencies S
            LEFT JOIN tblAccounts A ON S.AccountID = A.ID
            LEFT JOIN tblAccounts F ON S.FundAccountID = F.ID
            LEFT JOIN tblCurrencies C1 ON S.CurrencyID = C1.ID
            LEFT JOIN tblCurrencies C2 ON S.ExchangeCurrencyID = C2.ID
            LEFT JOIN tblUsers U ON S.CreatedBy = U.ID
            LEFT JOIN tblBranches BR ON S.BranchID = BR.ID
            WHERE S.isDeleted = 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vwsellcurrencies");
        DB::statement("DROP VIEW IF EXISTS vwgroups");
        DB::statement("DROP VIEW IF EXISTS vwbuycurrencies");
        DB::statement("DROP VIEW IF EXISTS vwaccountlimits");
        DB::statement("DROP VIEW IF EXISTS vw_userlist");
        DB::statement("DROP VIEW IF EXISTS vw_usergroups");
        DB::statement("DROP VIEW IF EXISTS vw_trialbalancehierarchical");
        DB::statement("DROP VIEW IF EXISTS vw_simpleentries");
        DB::statement("DROP VIEW IF EXISTS vw_historyview");
        DB::statement("DROP VIEW IF EXISTS vw_customerreceipts");
        DB::statement("DROP VIEW IF EXISTS vw_customerpayments");
        DB::statement("DROP VIEW IF EXISTS vw_currencyprices");
        DB::statement("DROP VIEW IF EXISTS vw_currencies");
        DB::statement("DROP VIEW IF EXISTS vw_accountstatementsummary");
        DB::statement("DROP VIEW IF EXISTS vw_accountstatementreport");
        DB::statement("DROP VIEW IF EXISTS vw_accountstatement");
        DB::statement("DROP VIEW IF EXISTS vw_accounts");
    }
};
