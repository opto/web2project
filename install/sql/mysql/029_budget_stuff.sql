
ALTER TABLE `billingcode` CHANGE `company_id` `company_id` INT( 10 ) NOT NULL DEFAULT '0'
ALTER TABLE `billingcode` ADD `billingcode_category` VARCHAR( 50 ) NOT NULL DEFAULT '';

INSERT INTO sysvals (sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES
    (1, 'BudgetCategory', 'Consulting', 'consulting'),
    (1, 'BudgetCategory', 'Hardware', 'hardware'),
    (1, 'BudgetCategory', 'Licenses', 'licenses'),
    (1, 'BudgetCategory', 'Permits', 'permits'),
    (1, 'BudgetCategory', 'Travel', 'travel');