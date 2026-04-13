<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Trigger: تحديث الرصيد عند إضافة سطر تفصيلي جديد (فقط إذا كان القيد مرحلاً)
        DB::unprepared("
            CREATE TRIGGER trig_EntryDetails_AfterInsert
            AFTER INSERT ON tblEntryDetails
            FOR EACH ROW
            BEGIN
                DECLARE is_posted INT DEFAULT 0;
                DECLARE is_deleted INT DEFAULT 0;
                DECLARE branch_id INT;

                SELECT IsPosted, isDeleted, BranchID INTO is_posted, is_deleted, branch_id 
                FROM tblEntries WHERE ID = NEW.ParentID;

                IF is_posted = 1 AND is_deleted = 0 THEN
                    INSERT INTO tblAccountBalances (AccountID, CurrencyID, BranchID, Balance, LastUpdated)
                    VALUES (NEW.AccountID, NEW.CurrencyID, branch_id, NEW.Amount, NOW())
                    ON DUPLICATE KEY UPDATE 
                        Balance = Balance + NEW.Amount,
                        LastUpdated = NOW();
                END IF;
            END;
        ");

        // 2. Trigger: تحديث الرصيد عند تعديل مبلغ أو حساب في سطر قيد موجود
        DB::unprepared("
            CREATE TRIGGER trig_EntryDetails_AfterUpdate
            AFTER UPDATE ON tblEntryDetails
            FOR EACH ROW
            BEGIN
                DECLARE is_posted INT DEFAULT 0;
                DECLARE branch_id INT;

                SELECT IsPosted, BranchID INTO is_posted, branch_id 
                FROM tblEntries WHERE ID = NEW.ParentID;

                IF is_posted = 1 THEN
                    -- طرح القيمة القديمة
                    UPDATE tblAccountBalances SET Balance = Balance - OLD.Amount, LastUpdated = NOW()
                    WHERE AccountID = OLD.AccountID AND CurrencyID = OLD.CurrencyID AND BranchID = branch_id;
                    
                    -- إضافة القيمة الجديدة
                    INSERT INTO tblAccountBalances (AccountID, CurrencyID, BranchID, Balance, LastUpdated)
                    VALUES (NEW.AccountID, NEW.CurrencyID, branch_id, NEW.Amount, NOW())
                    ON DUPLICATE KEY UPDATE 
                        Balance = Balance + NEW.Amount,
                        LastUpdated = NOW();
                END IF;
            END;
        ");

        // 3. Trigger: طرح المبلغ من الرصيد عند حذف سطر تفصيلي
        DB::unprepared("
            CREATE TRIGGER trig_EntryDetails_AfterDelete
            AFTER DELETE ON tblEntryDetails
            FOR EACH ROW
            BEGIN
                DECLARE is_posted INT DEFAULT 0;
                DECLARE branch_id INT;

                SELECT IsPosted, BranchID INTO is_posted, branch_id 
                FROM tblEntries WHERE ID = OLD.ParentID;

                IF is_posted = 1 THEN
                    UPDATE tblAccountBalances SET Balance = Balance - OLD.Amount, LastUpdated = NOW()
                    WHERE AccountID = OLD.AccountID AND CurrencyID = OLD.CurrencyID AND BranchID = branch_id;
                END IF;
            END;
        ");

        // 4. Trigger: الترحيل أو الحذف (يحدث كافة الأرصدة دفعة واحدة)
        DB::unprepared("
            CREATE TRIGGER trig_Entries_AfterUpdate
            AFTER UPDATE ON tblEntries
            FOR EACH ROW
            BEGIN
                -- حالة الترحيل: تحويل من مسودة (0) إلى مرحل (1)
                IF OLD.IsPosted = 0 AND NEW.IsPosted = 1 AND NEW.isDeleted = 0 THEN
                    INSERT INTO tblAccountBalances (AccountID, CurrencyID, BranchID, Balance, LastUpdated)
                    SELECT AccountID, CurrencyID, NEW.BranchID, SUM(Amount), NOW()
                    FROM tblEntryDetails WHERE ParentID = NEW.ID
                    GROUP BY AccountID, CurrencyID
                    ON DUPLICATE KEY UPDATE 
                        tblAccountBalances.Balance = tblAccountBalances.Balance + VALUES(Balance),
                        tblAccountBalances.LastUpdated = NOW();
                END IF;

                -- حالة الحذف: تحويل من مرحل إلى محذوف
                IF NEW.isDeleted = 1 AND OLD.isDeleted = 0 AND OLD.IsPosted = 1 THEN
                    UPDATE tblAccountBalances b
                    JOIN tblEntryDetails d ON b.AccountID = d.AccountID AND b.CurrencyID = d.CurrencyID
                    SET b.Balance = b.Balance - d.Amount, b.LastUpdated = NOW()
                    WHERE d.ParentID = NEW.ID AND b.BranchID = NEW.BranchID;
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trig_EntryDetails_AfterInsert;");
        DB::unprepared("DROP TRIGGER IF EXISTS trig_EntryDetails_AfterUpdate;");
        DB::unprepared("DROP TRIGGER IF EXISTS trig_EntryDetails_AfterDelete;");
        DB::unprepared("DROP TRIGGER IF EXISTS trig_Entries_AfterUpdate;");
    }
};
