<?php

Route::group(['middleware' => ['auth:api']], function () {
    // ONLY MEMBER AND ADMIN ACCOUNT ALLOWED
    Route::get('/user_data', 'Admin\AdminController@user_data');
    Route::post('/image/upload', 'Member\MemberController@upload_image');
    Route::post('/video/upload', 'Member\MemberController@upload_video');
    Route::post('/service/charge', 'Member\MemberController@get_service_charge');
    Route::post('/logout', 'SecretController@logout');

     //REWARD POINTS by Centy 05-08-24
     Route::post('/reward_points/get_data', 'Admin\AdminRewardPointsController@getData');
     Route::post('/reward_points/get_item', 'Member\MemberRewardPointsController@get_item');
     Route::post('/reward_points/claimedRewardItem', 'Member\MemberRewardPointsController@claimedRewardItem');
     Route::post('/reward_points/getClaimedRewardItem', 'Member\MemberRewardPointsController@getClaimedRewardItem');
     Route::post('/reward_points/list_of_claimed_redemption_items', 'Member\MemberRewardPointsController@list_of_claimed_redemption_items');
     Route::post('/reward_points/claimed_redemption_item_change_status', 'Member\MemberRewardPointsController@claimed_redemption_item_change_status');

    //ACHIEVERS RANK // Created By: Centy - 10-27-2023
    Route::post('/achievers_rewards/list_of_claimed_achievers_rewards', 'Admin\AdminAchieversRankController@list_of_claimed_achievers_rewards');
    Route::post('/achievers_rewards/achievers_approval', 'Admin\AdminAchieversRankController@achievers_approval');

    //INCENTIVE
    Route::post('/incentive/get_item', 'Member\MemberIncentiveController@get_item');
    Route::post('/incentive/claimedRewardItem', 'Member\MemberIncentiveController@claimedRewardItem');
    Route::post('/incentive/getClaimedRewardItem', 'Member\MemberIncentiveController@getClaimedRewardItem');

    //CASH IN/OUT
    Route::post('/cashin/get_transactions', 'Member\MemberCashInController@get_transactions');
    Route::post('/cashout/get_transactions', 'Admin\AdminCashOutController@get_transactions');
    Route::post('/cashin/get_method_list', 'Member\MemberCashInController@get_method_list');
    Route::post('/cashout/get_method_list', 'Admin\AdminCashOutController@get_method_list');
    Route::post('/cashout/get_method_list_raw', 'Admin\AdminCashOutController@get_method_list_raw');
    Route::post('/cashout/get_settings', 'Admin\AdminCashOutController@get_settings');
    Route::post('/cashout/update_settings', 'Admin\AdminCashOutController@update_settings');

    //INVESTMENT
    Route::post('/investment/get_investment_list', 'Member\MemberInvestMentController@get_investment_list');
    Route::post('/investment/get_package_list', 'Member\MemberInvestMentController@get_package_list');
    Route::post('/investment/preview', 'Member\MemberInvestMentController@investment_preview');
    Route::post('/investment/submit', 'Member\MemberInvestMentController@investment_submit');
    Route::post('/investment/details', 'Member\MemberInvestMentController@investment_details');
    Route::post('/investment/get_minimum_amount', 'Member\MemberInvestMentController@get_minimum_amount');

});

Route::group(['middleware' => ['auth:api', 'admin']], function () {
    /*ADMIN MANAGE SETTINGS*/
    Route::post('/settings/seed', 'Admin\AdminSettingsController@seed');
    Route::post('/settings/codevault', 'Admin\AdminSettingsController@codevault');
    Route::post('/settings/codevault_update', 'Admin\AdminSettingsController@codevault_update');
    Route::post('/settings/load_shipping_info', 'Admin\AdminSettingsController@load_shipping_info');
    Route::post('/settings/load_breakdown_items', 'Admin\AdminSettingsController@load_breakdown_items');
    Route::post('/settings/update_breakdown_items', 'Admin\AdminSettingsController@update_breakdown_items');
    Route::post('/settings/manage_shipping_fee', 'Admin\AdminSettingsController@manage_shipping_fee');
    Route::post('/settings/lockdown_settings', 'Admin\AdminSettingsController@lockdown_settings');
    Route::post('/settings/lockdown_settings_update', 'Admin\AdminSettingsController@lockdown_settings_update');
    Route::post('/settings/retailer', 'Admin\AdminSettingsController@retailer');
    Route::post('/settings/retailer_update', 'Admin\AdminSettingsController@retailer_update');
    Route::post('/settings/codeactivate', 'Admin\AdminSettingsController@codeactivate');
    Route::post('/settings/codeactivate_update', 'Admin\AdminSettingsController@codeactivate_update');
    Route::post('/settings/slot', 'Admin\AdminSettingsController@slot');
    Route::post('/settings/slot_update', 'Admin\AdminSettingsController@slot_update');
    Route::post('/settings/registration', 'Admin\AdminSettingsController@registration');
    Route::post('/settings/registration_update', 'Admin\AdminSettingsController@registration_update');
    Route::post('/settings/load_tin_settings', 'Admin\AdminSettingsController@load_tin_settings');
    Route::post('/settings/update_tin_settings', 'Admin\AdminSettingsController@update_tin_settings');
    Route::post('/settings/load_income_limit_settings', 'Admin\AdminSettingsController@load_income_limit_settings');
    Route::post('/settings/update_income_limit_settings', 'Admin\AdminSettingsController@update_income_limit_settings');
    Route::post('/settings/load_leaderboard', 'Admin\AdminSettingsController@load_leaderboard');
    Route::post('/settings/update_leaderboard', 'Admin\AdminSettingsController@update_leaderboard');
    Route::post('/admin/get_company_details', 'Admin\AdminController@get_company_details');
    Route::post('/admin/cashout_receipt_data', 'Admin\AdminPayoutController@cashout_receipt_data');

    Route::post('/settings/edit_receipt_info', 'Admin\AdminCashierController@edit_receipt_info');
    Route::post('/settings/load_receipt_info', 'Admin\AdminCashierController@load_receipt_info');
    Route::post('/settings/load_cashier_bonus', 'Admin\AdminCashierController@load_cashier_bonus');
    Route::post('/settings/manage_cashier_bonus', 'Admin\AdminCashierController@manage_cashier_bonus');

    // REWARD POINTS by Centy 05-08-24
    Route::post('/reward_points/store', 'Admin\AdminRewardPointsController@store');
    Route::get('/reward_points/show/{id}', 'Admin\AdminRewardPointsController@show');
    Route::post('/reward_points/update/{id}', 'Admin\AdminRewardPointsController@update');
    Route::get('/reward_points/destroy/{id}', 'Admin\AdminRewardPointsController@destroy');
    Route::get('/reward_points/restore/{id}', 'Admin\AdminRewardPointsController@restore');
    Route::get('/reward_points/get_settings', 'Admin\AdminRewardPointsController@get_settings');

    //INCENTIVE
    Route::post('/incentive/get_data', 'Admin\AdminIncentiveController@getData');
    Route::post('/incentive/store', 'Admin\AdminIncentiveController@store');
    Route::get('/incentive/show/{id}', 'Admin\AdminIncentiveController@show');
    Route::post('/incentive/update/{id}', 'Admin\AdminIncentiveController@update');
    Route::get('/incentive/destroy/{id}', 'Admin\AdminIncentiveController@destroy');
    Route::get('/incentive/restore/{id}', 'Admin\AdminIncentiveController@restore');
    Route::post('/incentive/list_of_claimed_redemption_items', 'Admin\AdminIncentiveController@list_of_claimed_redemption_items');
    Route::post('/incentive/claimed_redemption_item_change_status', 'Admin\AdminIncentiveController@claimed_redemption_item_change_status');

    /*FOR CUTOMIZED DATA*/
    Route::post('/customize_data/get', 'Admin\AdminCustomizeController@get');
    Route::post('/genealogy_data/get', 'Admin\AdminCustomizeController@get_genealogy_data');
    Route::post('/genealogy_data/save', 'Admin\AdminCustomizeController@save_genealogy_data');
    Route::post('/customize_data/update', 'Admin\AdminCustomizeController@update');
    /*END OF CUSTOMIZED DATA*/
    Route::post('/get_admin_access', 'Admin\AdminController@get_admin_access');
    Route::post('/audit_login_trail', 'Admin\AdminController@audit_login_trail');
    Route::post('/audit_trail/get', 'Admin\AdminController@get_audit_trail');
    Route::post('/audit_trail/get_actions', 'Admin\AdminController@get_audit_actions');
    Route::post('/get_logo', 'Admin\AdminController@get_logo');
    Route::post('/get_user_details', 'Admin\AdminController@get_user_details');
    Route::post('/mlm_feature', 'Admin\AdminController@mlm_feature');
    Route::post('/get_plan_label', 'Admin\AdminController@get_plan_label');

    // ONLY ADMIN ACCOUNT ALLOWED
    Route::post('/get_membership', 'Admin\AdminController@get_membership');
    Route::post('/get_product', 'Admin\AdminController@get_product');
    Route::post('/get_product_unilevel', 'Admin\AdminController@get_product_unilevel');
    Route::post('/get_product_stairstep', 'Admin\AdminController@get_product_stairstep');
    Route::post('/get_ldautoship', 'Admin\AdminController@get_ldautoship');
    Route::post('/save_ldautoship', 'Admin\AdminController@save_ldautoship');
    Route::post('/admin/get_random_code', 'Admin\AdminCodeController@get_random_code');
    Route::post('/save_product_unilevel', 'Admin\AdminController@save_product_unilevel');
    Route::post('/save_product_stairstep', 'Admin\AdminController@save_product_stairstep');
    Route::post('/load_lockdown', 'Admin\AdminController@load_lockdown');
    Route::post('/load_lockdown_save', 'Admin\AdminController@load_lockdown_save');
    Route::post('/load_lockdown_settings', 'Admin\AdminController@load_lockdown_settings');

    Route::post('/admin/reset_data', 'Admin\AdminResetController@reset_data');
    /*ADMIN DASHBOARD*/
    Route::post('/admin/dashboard_figures', 'Admin\AdminDashboardController@dashboard_figures');
    Route::post('/admin/visitor_data', 'Admin\AdminDashboardController@visitor_data');
    Route::post('/admin/visit_chart_data', 'Admin\AdminDashboardController@visit_chart_data');
    Route::post('/admin/member_chart_data', 'Admin\AdminDashboardController@member_chart_data');
    Route::post('/admin/sales_chart_data', 'Admin\AdminDashboardController@sales_chart_data');
    Route::post('/admin/get_top_list', 'Admin\AdminDashboardController@get_top_list');
    Route::post('/admin/loadplan_list', 'Admin\AdminDashboardController@loadplan_list');
    Route::post('/admin/load_topearner', 'Admin\AdminDashboardController@load_topearner');
    Route::post('/admin/load_topearner_accummulated', 'Admin\AdminDashboardController@load_topearner_accummulated');
    Route::post('/admin/load_topdirect', 'Admin\AdminDashboardController@load_topdirect');
    
    /* PRODUCT */
    Route::post('/product/add', 'Admin\AdminProductController@add');
    Route::post('/product/edit', 'Admin\AdminProductController@edit');
    Route::post('/product/get', 'Admin\AdminProductController@get');
    Route::post('/product/restock', 'Admin\AdminProductController@restock');
    Route::post('/product/get_inventory', 'Admin\AdminProductController@get_inventory');
    Route::post('/product/get_item_inventory', 'Admin\AdminProductController@get_item_inventory');
    Route::post('/product/get_item_code', 'Admin\AdminProductController@get_item_code');
    Route::post('/product/archive', 'Admin\AdminProductController@archive');
    Route::post('/product/add_item', 'Admin\AdminProductController@add_item');
    Route::post('/product/unarchive', 'Admin\AdminProductController@unarchive');
    Route::post('/product/data', 'Admin\AdminProductController@data');
    Route::post('/product/get_currency', 'Admin\AdminProductController@get_currency');
    Route::post('/product/generate_codes', 'Admin\AdminProductController@generate_codes');
    Route::post('/product/load_island_group', 'Admin\AdminProductController@load_island_group');
    Route::post('/product/load_shipping_fee', 'Admin\AdminProductController@load_shipping_fee');
    Route::post('/product/get_category_list', 'Admin\AdminProductController@get_category_list');
    Route::post('/product/get_subcategory_list', 'Admin\AdminProductController@get_subcategory_list');
    Route::post('/product/highest_membership_list', 'Admin\AdminProductController@highest_membership_list');
    Route::post('/product/load_team_sales_bonus_level', 'Admin\AdminProductController@load_team_sales_bonus_level');
    Route::post('/product/recount_inventory', 'Admin\AdminProductController@recount_inventory');
    Route::post('/product/check_stocks', 'Admin\AdminProductController@check_stocks');

    //AdminBranch Routes
    Route::post('/branch/cashier/add_branch', 'Admin\AdminBranchController@add_branch');
    Route::post('/branch/cashier/get_branch', 'Admin\AdminBranchController@get_branch');
    Route::post('/branch/cashier/data', 'Admin\AdminBranchController@data');
    Route::post('/branch/cashier/archive', 'Admin\AdminBranchController@archive');
    Route::post('/branch/cashier/restore', 'Admin\AdminBranchController@restore');
    Route::post('/branch/cashier/edit', 'Admin\AdminBranchController@edit');
    Route::post('/branch/cashier/search', 'Admin\AdminBranchController@search');
    Route::post('/branch/cashier/get_stockist', 'Admin\AdminBranchController@get_stockist');
    Route::post('/branch/cashier/get_access_list', 'Admin\AdminBranchController@get_access_list');
    Route::post('/branch/cashier/add_stockist_level', 'Admin\AdminBranchController@add_stockist_level');
    Route::post('/branch/cashier/archive_stockist_level', 'Admin\AdminBranchController@archive_stockist_level');
    Route::post('/branch/cashier/access_list_submit', 'Admin\AdminBranchController@access_list_submit');
    Route::post('/branch/cashier/save_company_info', 'Admin\AdminBranchController@save_company_info');
    Route::post('/branch/cashier/load_company_info', 'Admin\AdminBranchController@load_company_info');

    //AdminCashier
    Route::post('/branch/cashier/add_cashier', 'Admin\AdminCashierController@add_cashier');
    Route::post('/branch/cashier/get_cashier', 'Admin\AdminCashierController@get_cashierList');
    Route::post('/branch/cashier/edit_cashier', 'Admin\AdminCashierController@edit_cashier');
    Route::post('/branch/cashier/edit_password', 'Admin\AdminCashierController@edit_password');
    Route::post('/branch/cashier/edit_cashier_submit', 'Admin\AdminCashierController@edit_cashier_submit');
    Route::post('/branch/cashier/add_location', 'Admin\AdminCashierController@add_location');
    Route::post('/branch/cashier/get_location', 'Admin\AdminCashierController@get_location');
    Route::post('/branch/cashier/archive_location', 'Admin\AdminCashierController@archive_location');
    Route::post('/branch/cashier/release_service_income', 'Admin\AdminCashierController@release_service_income');
    Route::post('/branch/cashier/get_payment_method', 'Admin\AdminCashierController@get_payment_method');
    Route::post('/branch/cashier/set_payment_method', 'Admin\AdminCashierController@set_payment_method');
    Route::post('/branch/cashier/add_payment_method', 'Admin\AdminCashierController@add_payment_method');

    //AdminCode
    Route::post('/branch/cashier/generate_codes', 'Admin\AdminCodeController@generate_codes');
    Route::post('/branch/cashier/get_codes', 'Admin\AdminCodeController@get_codes');
    Route::post('/branch/cashier/delete_code', 'Admin\AdminCodeController@delete_code');

    //AdminPayout
    Route::post('/payout/charge_settings', 'Admin\AdminPayoutController@charge_settings');
    Route::post('/payout/get_charge_settings', 'Admin\AdminPayoutController@get_charge_settings');
    Route::post('/payout/payout_configuration', 'Admin\AdminPayoutController@payout_configuration');

    /* COUNTRY */
    Route::post('/country/get', 'Admin\AdminCountryController@get');

    /* MEMBER */
    Route::post('/member/get', 'Admin\AdminMemberController@get');
    Route::post('/member/slot_info', 'Admin\AdminMemberController@slot_info');
    Route::post('/member/select_users', 'Admin\AdminMemberController@select_user');
    Route::post('/member/add_member', 'Admin\AdminMemberController@add');
    Route::post('/member/add_slot', 'Admin\AdminMemberController@add_slot');
    Route::post('/member/get_unplaced', 'Admin\AdminMemberController@get_unplaced');
    Route::post('/member/slot_limit', 'Admin\AdminMemberController@slot_limit');
    Route::post('/member/update_slot_limit', 'Admin\AdminMemberController@update_slot_limit');
    Route::post('/member/get_plan_list', 'Admin\AdminMemberController@get_plan_list');
    Route::post('/member/adjust_wallet', 'Admin\AdminMemberController@adjust_wallet');
    Route::post('/member/place_slot', 'Admin\AdminMemberController@place_slot');
    Route::post('/member/get_slot_information', 'Admin\AdminMemberController@get_slot_information');
    Route::post('/member/submit_slot_information', 'Admin\AdminMemberController@submit_slot_information');
    Route::post('/member/get_slot_details', 'Admin\AdminMemberController@get_slot_details');
    Route::post('/member/get_slot_earnings', 'Admin\AdminMemberController@get_slot_earnings');
    Route::post('/member/get_slot_distributed', 'Admin\AdminMemberController@get_slot_distributed');
    Route::post('/member/get_slot_wallet', 'Admin\AdminMemberController@get_slot_wallet');
    Route::post('/member/get_slot_payout', 'Admin\AdminMemberController@get_slot_payout');
    Route::post('/member/get_slot_points', 'Admin\AdminMemberController@get_slot_points');
    Route::post('/member/get_slot_network', 'Admin\AdminMemberController@get_slot_network');
    Route::post('/member/get_slot_codevault', 'Admin\AdminMemberController@get_slot_codevault');
    Route::post('/member/get_auto_position', 'Admin\AdminMemberController@get_auto_position');
    Route::post('/member/initialize_recompute', 'Admin\AdminMemberController@initialize_recompute');
    Route::post('/member/recompute_sponsor', 'Admin\AdminMemberController@recompute_sponsor');
    Route::post('/member/recompute_placement', 'Admin\AdminMemberController@recompute_placement');
    Route::post('/member/import_excel', 'Admin\AdminMemberController@import_excel');
    Route::post('/member/slot_code_history', 'Admin\AdminMemberController@slot_code_history');
    Route::post('/member/user_verification', 'Admin\AdminMemberController@user_verification');

    /* SLOT */
    Route::post('/slot/get', 'Admin\AdminSlotController@get');
    Route::post('/slot/get_full', 'Admin\AdminSlotController@get_full');
    Route::post('/slot/get_full_unilevel', 'Admin\AdminSlotController@get_full_unilevel');
    Route::post('/slot/get_unilevel_list', 'Admin\AdminSlotController@get_unilevel_list');
    Route::post('/slot/get_unplaced', 'Admin\AdminSlotController@get_unplaced');
    Route::post('/slot/get_filters', 'Admin\AdminSlotController@get_filters');
    Route::post('/slot/distribute_start', 'Admin\AdminUnilevelController@distribute_start');
    Route::post('/slot/distribute_unilevel_slot', 'Admin\AdminUnilevelController@distribute_slot');
    Route::post('/slot/distributetwo_start', 'Admin\AdminUnilevelTwoController@distribute_start');
    Route::post('/slot/distributetwo_unilevel_slot', 'Admin\AdminUnilevelTwoController@distribute_slot');
    Route::post('/slot/get_currency', 'Admin\AdminSlotController@get_currency');

    /* MEMBERSHIP */
    Route::post('/membership/get', 'Admin\AdminMembershipController@get');
    Route::post('/membership/get_manage_settings', 'Admin\AdminMembershipController@get_manage_settings');
    Route::post('/membership/submit', 'Admin\AdminMembershipController@submit');

    /* PLAN */
    Route::post('/plan/get', 'Admin\AdminPlanController@get');
    Route::post('/plan/update', 'Admin\AdminPlanController@update');
    Route::post('/plan/update_board', 'Admin\AdminPlanController@update_board');
    Route::post('/plan/load_board_settings', 'Admin\AdminPlanController@load_board_settings');
    Route::post('/plan/update_status', 'Admin\AdminPlanController@update_status');
    Route::post('/plan/update_membership_upgrade', 'Admin\AdminPlanController@update_membership_upgrade');
    Route::post('/plan/update_personal_cashback', 'Admin\AdminPlanController@update_personal_cashback');
    Route::post('/plan/update_sign_up_bonus', 'Admin\AdminPlanController@update_sign_up_bonus');
    Route::post('/plan/update_retailer_commission', 'Admin\AdminPlanController@update_retailer_commission');
    Route::post('/plan/update_share_link_v2', 'Admin\AdminPlanController@update_share_link_v2');
    Route::post('/plan/update_direct_personal_cashback', 'Admin\AdminPlanController@update_direct_personal_cashback');

    /*CURRENCY*/
    Route::post('/currency/get', 'Admin\AdminPlanController@currency_get');
    Route::post('/currency/update', 'Admin\AdminPlanController@currency_update');
    /*INVESTMENT*/
    Route::post('/investment_package/get', 'Admin\AdminPlanController@investment_package_get');
    Route::post('/investment_package/submit', 'Admin\AdminPlanController@investment_package_submit');
    Route::post('/investment/getinvestment_amount', 'Admin\AdminPlanController@get_investment_amount');
    Route::post('/investment/update_investment_amount', 'Admin\AdminPlanController@update_investment_amount');
    /* CASH IN */
    Route::post('/cashin/get_method_category_list', 'Admin\AdminCashInController@get_method_category_list');
    Route::post('/cashin/add_new_method', 'Admin\AdminCashInController@add_new_method');
    Route::post('/cashin/update_method', 'Admin\AdminCashInController@update_method');
    Route::post('/cashin/archive_method', 'Admin\AdminCashInController@archive_method');
    Route::post('/cashin/process_transaction', 'Admin\AdminCashInController@process_transaction');
    Route::post('/cashin/process_all_transaction', 'Admin\AdminCashInController@process_all_transaction');
    Route::post('/cashin/get_currency', 'Admin\AdminCashInController@get_currency');

    /* CASH OUT */
    Route::post('/cashout/add_new_method', 'Admin\AdminCashOutController@add_new_method');
    Route::post('/cashout/trigger_gc', 'Admin\AdminCashOutController@trigger_gc');
    Route::post('/cashout/update_method', 'Admin\AdminCashOutController@update_method');
    Route::post('/cashout/archive_method', 'Admin\AdminCashOutController@archive_method');
    Route::post('/cashout/check_schedule', 'Admin\AdminCashOutController@check_schedule');
    Route::post('/cashout/check_schedule_details', 'Admin\AdminCashOutController@check_schedule_details');
    Route::post('/cashout/process_payout', 'Admin\AdminCashOutController@process_payout');
    Route::post('/cashout/get_schedules', 'Admin\AdminCashOutController@get_schedules');
    Route::post('/cashout/update_transaction', 'Admin\AdminCashOutController@update_transaction');
    // Route::post('/cashout/process_transaction', 'Admin\AdminCashOutController@process_transaction');
    Route::post('/cashout/process_transactions', 'Admin\AdminCashOutController@process_transactions');
    Route::post('/cashout/check_negatives', 'Admin\AdminCashOutController@check_negatives');
    Route::post('/cashout/get_actual_schedule_transactions', 'Admin\AdminCashOutController@get_actual_schedule_transactions');
    Route::post('/cashout/import_payout', 'Admin\AdminCashOutController@import_payout');
    Route::post('/cashout/update_message', 'Admin\AdminCashOutController@update_message');

    /*ADMIN ELOADING*/
    Route::post('/eloading/import_excel', 'Admin\AdminEloadingController@import_excel');
    Route::post('/eloading/get_eload_product', 'Admin\AdminEloadingController@get_eload_product');
    Route::post('/eloading/get_settings', 'Admin\AdminEloadingController@get_settings');
    Route::post('/eloading/reset_product', 'Admin\AdminEloadingController@reset_product');
    Route::post('/eloading/get_eload_logs', 'Admin\AdminEloadingController@get_eload_logs');

    /*Orders*/
    Route::post('/orders/get_orders', 'Admin\AdminOrderController@get');
    Route::post('/orders/select_order', 'Admin\AdminOrderController@select');
    Route::post('/orders/change_status', 'Admin\AdminOrderController@status');
    Route::post('/orders/charge_table', 'Admin\AdminOrderController@charge_table');
    Route::post('/orders/currency_default', 'Admin\AdminOrderController@currency_default');
    Route::post('/orders/edit_delivery_charge', 'Admin\AdminOrderController@edit_delivery_charge');
    Route::post('/orders/save_orders_method', 'Admin\AdminOrderController@save_orders_method');
    Route::post('/orders/select_claim_code', 'Admin\AdminOrderController@select_claim_code');
    Route::post('/orders/update_claim_code', 'Admin\AdminOrderController@update_claim_code');
    Route::post('/orders/claim_code_list', 'Admin\AdminOrderController@get_claim_code_list');
    Route::post('/orders/updateOrderInfo', 'Admin\AdminOrderController@updateOrderInfo');
    Route::post('/orders/get_dragonpay_orders', 'Admin\AdminOrderController@get_dragonpay_orders');
    Route::post('/orders/get_for_approvals', 'Admin\AdminOrderController@get_for_approvals');
    Route::post('/orders/view_information', 'Admin\AdminOrderController@view_information');
    Route::post('/orders/update_transaction', 'Admin\AdminOrderController@update_transaction');

    /*Unilevel Orabella*/
    Route::post('/unilevelorabella/unilevel_get', 'Admin\AdminUnilevelOrabellaController@get');
    Route::post('/unilevelorabella/unilevel_get_distribute', 'Admin\AdminUnilevelOrabellaController@unilevel_get_distribute');
    Route::post('/unilevelorabella/unilevel_filtered', 'Admin\AdminUnilevelOrabellaController@get_filtered');
    Route::post('/unilevelorabella/unilevel_distribute_points', 'Admin\AdminUnilevelOrabellaController@unilevel_distribute_points');
    Route::post('/unilevelorabella/unilevel_or_points_reset', 'Admin\AdminUnilevelOrabellaController@unilevel_or_points_reset');

    /*Personal Cashback*/
    Route::post('/personalcashback/get', 'Admin\AdminDistributePersonalController@get');
    Route::post('/personalcashback/get_distribute', 'Admin\AdminDistributePersonalController@get_distribute');
    Route::post('/personalcashback/filtered', 'Admin\AdminDistributePersonalController@filtered');
    Route::post('/personalcashback/distribute_points', 'Admin\AdminDistributePersonalController@distribute_points');

    /*GLOBAL POOL BONUS*/
    // Route::post('/distribute/global_pool',           'Admin\AdminGlobalPoolController@global_pool');
    Route::post('/plan/update_global', 'Admin\AdminGlobalPoolController@global_pool_bonus');
    Route::post('/pool_bonus/get', 'Admin\AdminGlobalPoolController@get');
    Route::post('/pool_bonus/get_distribute', 'Admin\AdminGlobalPoolController@get_distribute');
    Route::post('/pool_bonus/filtered', 'Admin\AdminGlobalPoolController@filtered');
    Route::post('/pool_bonus/distribute_points', 'Admin\AdminGlobalPoolController@distribute_points');
    Route::post('/pool_bonus/get_intitled', 'Admin\AdminGlobalPoolController@get_intitled');
    Route::post('/pool_bonus/check_intitled', 'Admin\AdminGlobalPoolController@check_intitled');
    Route::post('/pool_bonus/done_distribute', 'Admin\AdminGlobalPoolController@done_distribute');

    Route::post('/recompute/get_slot', 'Admin\AdminRecomputeController@get_slots_pass_up');
    Route::post('/recompute/get_slots_pass_up_all', 'Admin\AdminRecomputeController@get_slots_pass_up_all');
    Route::post('/recompute/recompute_slot', 'Admin\AdminRecomputeController@recompute_slot');
    Route::post('/recompute/recompute_single_pass_up', 'Admin\AdminRecomputeController@recompute_single_pass_up');
    Route::post('/recompute/check_password', 'Admin\AdminRecomputeController@check_password');

    Route::post('/recompute/check_password2', 'Admin\AdminRecomputeController@check_password2');
    Route::post('/recompute/recomputesingle', 'Admin\AdminRecomputeController@recomputesingle');
    Route::post('/recompute/check_password', 'Admin\AdminRecomputeController@check_password');
    Route::post('/recompute_membership/start', 'Admin\AdminRecomputeController@start');
    Route::post('/recompute/get_slots_binary', 'Admin\AdminRecomputeController@get_slots_binary');
    Route::post('/recompute/get_slots_binary_all', 'Admin\AdminRecomputeController@get_slots_binary_all');

    Route::post('/recompute/leveling_bonus_recompute', 'Admin\AdminRecomputeController@leveling_bonus_recompute');

    /*MAINTENANCE*/
    Route::post('/maintenance/module_settings', 'Admin\AdminMaintenanceController@module_settings');
    Route::post('/maintenance/get_module', 'Admin\AdminMaintenanceController@get_module');
    Route::post('/maintenance/update_module', 'Admin\AdminMaintenanceController@update_module');
    Route::post('/maintenance/create_position', 'Admin\AdminMaintenanceController@create_position');
    Route::post('/maintenance/create_admin', 'Admin\AdminMaintenanceController@create_admin');
    Route::post('/maintenance/get_position', 'Admin\AdminMaintenanceController@get_position');
    Route::post('/maintenance/get_admin', 'Admin\AdminMaintenanceController@get_admin');
    Route::post('/maintenance/update_admin', 'Admin\AdminMaintenanceController@update_admin');
    Route::post('/maintenance/import_slots', 'Admin\AdminMaintenanceController@import_slots');
    Route::post('/maintenance/import_member', 'Admin\AdminMaintenanceController@import_member');
    Route::post('/maintenance/import_custom_member', 'Admin\AdminMaintenanceController@import_custom_member');
    Route::post('/maintenance/import_member_slot', 'Admin\AdminMaintenanceController@import_member_slot');
    Route::post('/maintenance/get_cms_list', 'Admin\AdminMaintenanceController@get_cms_list');

    Route::post('/maintenance/get_other_settings', 'Admin\AdminMaintenanceController@get_other_settings');
    Route::post('/maintenance/update_other_settings', 'Admin\AdminMaintenanceController@update_other_settings');

    Route::post('/maintenance/import_placement', 'Admin\AdminMaintenanceController@import_placement');
    Route::post('/maintenance/import_adjust_wallet', 'Admin\AdminMaintenanceController@import_adjust_wallet');

    Route::post('/maintenance/cms_image/submit', 'Admin\AdminMaintenanceController@cms_image_submit');
    Route::post('/maintenance/update_gc', 'Admin\AdminMaintenanceController@update_gc');
    Route::post('/maintenance/load_gc', 'Admin\AdminMaintenanceController@load_gc');
    Route::post('/maintenance/save_logo', 'Admin\AdminMaintenanceController@save_logo');
    Route::post('/maintenance/load_dragonpay_settings', 'Admin\AdminMaintenanceController@load_dragonpay_settings');
    Route::post('/maintenance/update_dragonpay', 'Admin\AdminMaintenanceController@update_dragonpay');
    Route::post('/maintenance/get_user_details', 'Admin\AdminMaintenanceController@get_user_details');

    /*REPORTS*/
    Route::post('/report/qoute_request', 'Admin\AdminReportController@qoute_request');
    Route::post('/report/load_full_list', 'Admin\AdminReportController@load_full_list');
    Route::post('/report/topRecruiter_list', 'Admin\AdminReportController@topRecruiter_list');
    Route::post('/report/cashflow_list', 'Admin\AdminReportController@cashflow_list');
    Route::post('/report/bonussummary_list', 'Admin\AdminReportController@bonussummary_list');
    Route::post('/report/delete_qoute_request', 'Admin\AdminReportController@delete_qoute_request');
    Route::post('/report/recomputeTopRecruite', 'Admin\AdminReportController@recomputeTopRecruite');
    Route::post('/report/get_branch', 'Admin\AdminReportController@get_branch');
    Route::post('/report/load_sales_report', 'Admin\AdminReportController@load_sales_report');
    Route::post('/report/load_eload_report', 'Admin\AdminReportController@load_eload_report');
    Route::post('/report/load_month', 'Admin\AdminReportController@load_month');
    Route::post('/report/adjust_wallet', 'Admin\AdminReportController@adjust_wallet');
    Route::post('/report/achievers_report', 'Admin\AdminReportController@achievers_report'); // Created By: Centy - 10-27-2023
    Route::post('/report/achievers_report_full_list', 'Admin\AdminReportController@achievers_report_full_list'); // Created By: Centy - 10-27-2023
    Route::post('/report/code_transfer', 'Admin\AdminReportController@code_transfer');
    Route::post('/report/members_detail', 'Admin\AdminReportController@members_detail');
    Route::post('/report/unilevel_dynamic', 'Admin\AdminReportController@unilevel_dynamic');
    Route::post('/report/top_seller_report', 'Admin\AdminReportController@top_seller_report');
    Route::post('/report/sales_receipt', 'Admin\AdminReportController@sales_receipt');
    Route::post('/report/load_company_info', 'Admin\AdminReportController@load_company_info');
    Route::post('/report/promo_report', 'Admin\AdminReportController@promo_report');
    Route::post('/report/get_items', 'Admin\AdminReportController@get_items');
    Route::post('/report/load_survey', 'Admin\AdminReportController@get_survey_items');

    /*EXPORTS*/
    Route::post('/export/slot_wallet_history_pdf_ctr', 'Admin\AdminExportController@slot_wallet_history_pdf_ctr');
    Route::post('/export/slot_payout_history_pdf_ctr', 'Admin\AdminExportController@slot_payout_history_pdf_ctr');

    /*Video*/
    Route::post('/video/get', 'Admin\AdminVideoController@get');
    Route::post('/video/add_video', 'Admin\AdminVideoController@add_video');
    Route::post('/video/edit_video', 'Admin\AdminVideoController@edit_video');
    Route::post('/video/video_archived', 'Admin\AdminVideoController@video_archived');
    Route::post('/video/video_unarchived', 'Admin\AdminVideoController@video_unarchived');
    Route::post('/video/video_url_id', 'Admin\AdminVideoController@video_url_id');

    /*Survey*/
    Route::post('/survey/get', 'Admin\AdminSurveyController@get');
    Route::post('/survey/settings', 'Admin\AdminSurveyController@settings');
    Route::post('/survey/update_settings', 'Admin\AdminSurveyController@update_settings');
    Route::post('/survey/add', 'Admin\AdminSurveyController@add');
    Route::post('/survey/edit', 'Admin\AdminSurveyController@edit');
    Route::post('/survey/get/id', 'Admin\AdminSurveyController@id');
    Route::post('/survey/archived', 'Admin\AdminSurveyController@archived');

    // BANNER
    Route::post('/banner/add', 'Admin\AdminBannerController@add');
    Route::post('/banner/load_data', 'Admin\AdminBannerController@get_data');
    Route::post('/banner/edit', 'Admin\AdminBannerController@edit');
    Route::post('/banner/update', 'Admin\AdminBannerController@update');
    Route::post('/banner/delete', 'Admin\AdminBannerController@delete');
    Route::post('/banner/restore', 'Admin\AdminBannerController@restore');

    // EBOOKS
    Route::post('/ebooks/add', 'Admin\AdminEbooksController@add');
    Route::post('/ebooks/load_data', 'Admin\AdminEbooksController@get_data');
    Route::post('/ebooks/edit', 'Admin\AdminEbooksController@edit');
    Route::post('/ebooks/update', 'Admin\AdminEbooksController@update');
    Route::post('/ebooks/delete', 'Admin\AdminEbooksController@delete');
    Route::post('/ebooks/restore', 'Admin\AdminEbooksController@restore');
    Route::post('/ebooks/load_membership', 'Admin\AdminEbooksController@load_membership');
    Route::post('/ebooks/load_category', 'Admin\AdminEbooksController@load_category');
    Route::post('/ebooks/load_subcategory', 'Admin\AdminEbooksController@load_subcategory');
    Route::post('/ebooks/save_category', 'Admin\AdminEbooksController@save_category');

    // LIVE STREAMING
    Route::post('/live/stream/add', 'Admin\AdminLiveStreamingController@add');
    Route::post('/live/stream/load_data', 'Admin\AdminLiveStreamingController@get_data');
    Route::post('/live/stream/edit', 'Admin\AdminLiveStreamingController@edit');
    Route::post('/live/stream/update', 'Admin\AdminLiveStreamingController@update');
    Route::post('/live/stream/delete', 'Admin\AdminLiveStreamingController@delete');
    Route::post('/live/stream/restore', 'Admin\AdminLiveStreamingController@restore');

    // ANNOUNCEMENT
    Route::post('/announcement/add', 'Admin\AdminAnnouncementController@add');
    Route::post('/announcement/load_data', 'Admin\AdminAnnouncementController@get_data');
    Route::post('/announcement/edit', 'Admin\AdminAnnouncementController@edit');
    Route::post('/announcement/update', 'Admin\AdminAnnouncementController@update');
    Route::post('/announcement/delete', 'Admin\AdminAnnouncementController@delete');
    Route::post('/announcement/restore', 'Admin\AdminAnnouncementController@restore');

    // PRODUCT CATEGORY

    Route::post('/category/add_category', 'Admin\AdminProductCategoryController@add_category');
    Route::post('/category/load_data', 'Admin\AdminProductCategoryController@load_data');
    Route::post('/category/save_category', 'Admin\AdminProductCategoryController@save_category');
    Route::post('/category/edit', 'Admin\AdminProductCategoryController@edit');
    Route::post('/category/update_category', 'Admin\AdminProductCategoryController@update_category');
    Route::post('/category/delete_category', 'Admin\AdminProductCategoryController@delete_category');
});

Route::group(['middleware' => ['auth:api', 'member']], function () {

    Route::post('/get_products/detail', 'ApiController@get_product_view');

    Route::post('/member/get_eload_settings', 'Member\MemberEloadingController@get_eload_settings');

    Route::post('/test', 'Member\MemberController@test');
    Route::post('/member/genealogy/placement', 'Member\MemberGenealogyController@placement');
    Route::post('/member/genealogy/matrix', 'Member\MemberGenealogyController@matrix');
    Route::post('/member/genealogy/root_color', 'Member\MemberGenealogyController@root_color');
    Route::post('/member/genealogy/board', 'Member\MemberGenealogyController@board');
    Route::post('/member/genealogy/unilevel', 'Member\MemberGenealogyController@unilevel');
    Route::post('/member/genealogy/get_downline', 'Member\MemberGenealogyController@get_placement_downline');
    Route::post('/member/genealogy/get_matrix', 'Member\MemberGenealogyController@get_matrix_downline');
    Route::post('/member/genealogy/get_sponsor', 'Member\MemberGenealogyController@get_unilevel_downline');
    Route::post('/member/genealogy/what_show', 'Member\MemberGenealogyController@what_show');

    Route::post('/member/get_earning', 'Member\MemberEarningController@get');
    Route::post('/member/get_earning/label', 'Member\MemberEarningController@get_earning_label');
    Route::post('/member/get_initial', 'Member\MemberEarningController@get_initial');

    //MemberCodeController

    Route::post('/load_product_code', 'Member\MemberCodeController@load_product_code');
    Route::post('/load_membership_code', 'Member\MemberCodeController@load_membership_code');
    Route::post('/load_transfer_history_code', 'Member\MemberCodeController@load_transfer_history_code');
    Route::post('/get_codes', 'Member\MemberCodeController@get_member_codes');
    Route::post('/get_claim_codes', 'Member\MemberCodeController@get_claim_codes');
    Route::post('/member/get_filters', 'Member\MemberCodeController@get_filters');
    Route::post('/member/bulk_membership_transfer', 'Member\MemberCodeController@bulk_membership_transfer');
    Route::post('/member/bulk_membership_use', 'Member\MemberCodeController@bulk_membership_use');
    Route::post('/member/bulk_membership_add_member', 'Member\MemberCodeController@bulk_membership_add_member');
    Route::post('/member/bulk_use_product_code', 'Member\MemberCodeController@bulk_use_product_code');
    Route::post('/member/get_user_membership', 'Member\MemberCodeController@get_user_membership');
    Route::post('/member/get_own_slot_list', 'Member\MemberCodeController@get_own_slot_list');


    Route::post('/member/get_other_settings', 'Member\MemberDashboardController@get_other_settings');
    Route::post('/member/get_topEarner', 'Member\MemberDashboardController@get_topEarner');
    Route::post('/member/load_retailer_settings', 'Member\MemberDashboardController@load_retailer_settings');
    Route::post('/member/load_registered_retailer', 'Member\MemberDashboardController@load_registered_retailer');
    Route::post('/member/get_available_transfer_slots', 'Member\MemberDashboardController@get_available_transfer_slots');
    Route::post('/member/get_currency', 'Member\MemberDashboardController@currency_get');
    Route::post('/member/get_vortex', 'Member\MemberDashboardController@get_vortex');
    Route::post('/member/currency_converter', 'Member\MemberDashboardController@currency_converter');
    Route::post('/member/currency_converter_submit', 'Member\MemberDashboardController@currency_converter_submit');
    Route::post('/member/get_sponsor', 'Member\MemberDashboardController@get_sponsor');

    // BANNER
    Route::post('/member/load_banner', 'Member\MemberDashboardController@load_banner_member');

    // DRAGONPAY HISTORY
    Route::post('/member/dragonpay_history', 'Member\MemberDashboardController@dragonpay_history');

    //MemberProductController
    Route::post('/member/get_all_products', 'Member\MemberProductController@get_all_products');
    Route::post('/member/get_product', 'Member\MemberProductController@get_product');

    Route::post('/member/activate_product_code', 'Member\MemberProductController@activate_product_code');

    //Orders
    Route::post('/member/get_orders', 'Member\MemberOrderController@get_orders');
    Route::post('/member/claim_code_claimed', 'Member\MemberOrderController@claim_code_claimed');

    /*PLAN*/
    Route::post('/member/get_plan_settings', 'Member\MemberController@get_plan_settings');
    Route::post('/member/get_plan_label', 'Member\MemberController@get_plan_label');

    /*MODULE*/
    Route::post('/member/module_settings', 'Member\MemberController@module_settings');
    /*MAINTAIN*/
    Route::post('/member/check_if_maintain', 'Member\MemberController@check_if_maintain');
    Route::post('/member/check_item_unilevel', 'Member\MemberController@check_item_unilevel');
    Route::post('/member/check_item_stairstep', 'Member\MemberController@check_item_stairstep');
    Route::post('/member/check_item_lockdown', 'Member\MemberController@check_item_lockdown');

    Route::post('/current_slot', 'Member\MemberController@current_slot');
    Route::post('/all_slot', 'Member\MemberController@all_slot');
    Route::post('/get_total', 'Member\MemberController@get_total');

    Route::post('/cashout_receipt_data', 'Member\MemberController@cashout_receipt_data');
    Route::post('/get_company_details', 'Member\MemberController@get_company_details');

    Route::post('/member/upgrade_kit', 'Member\MemberController@upgrade_kit');

    Route::post('/member/check_user_info', 'Member\MemberController@check_user_info');

    // ACCOUNT ACTIVATION
    Route::post('/resend_verification', 'AccountActivationController@resend_verification');

    Route::post('/count_slot', 'Member\MemberController@count_slot');

    Route::post('/wallet_log', 'Member\MemberController@wallet_log');
    Route::post('/cashout_history', 'Member\MemberController@cashout_history');
    Route::post('/cashin_history', 'Member\MemberController@cashin_history');
    Route::post('/upgrade_history', 'Member\MemberController@upgrade_history');
    Route::post('/member/move_wallet', 'Member\MemberController@move_wallet');
    Route::post('/member/add_downline', 'Member\MemberController@add_downline');
    Route::post('/member/get_own_membership_list', 'Member\MemberController@get_own_membership_list');

    Route::post('/earning/direct', 'Member\MemberEarningController@direct_earning');
    Route::post('/earning/direct_gc', 'Member\MemberEarningController@direct_gc_earning');
    Route::post('/earning/direct_bonus', 'Member\MemberEarningController@direct_bonus_earning');
    Route::post('/earning/mentors', 'Member\MemberEarningController@mentors_bonus_earning');
    Route::post('/earning/sponsor_matching', 'Member\MemberEarningController@sponsor_matching_earning');
    Route::post('/earning/indirect', 'Member\MemberEarningController@indirect_earning');
    Route::post('/earning/indirect_details', 'Member\MemberEarningController@indirect_details');
    Route::post('/earning/binary', 'Member\MemberEarningController@binary_earning');
    Route::post('/earning/binary_points', 'Member\MemberEarningController@binary_points');
    Route::post('/earning/binary_slot_limit', 'Member\MemberEarningController@binary_slot_limit');
    Route::post('/earning/unilevel', 'Member\MemberEarningController@unilevel');
    Route::post('/earning/unilevel_dynamic', 'Member\MemberEarningController@unilevel_dynamic');
    Route::post('/earning/stairstep', 'Member\MemberEarningController@stairstep');
    Route::post('/earning/cashback', 'Member\MemberEarningController@cashback');
    Route::post('/earning/watch_earning', 'Member\MemberEarningController@watch_earn_earning');
    Route::post('/earning/global_pool', 'Member\MemberEarningController@global_pool_earning');
    Route::post('/earning/incentive_bonus', 'Member\MemberEarningController@incentive_bonus_earning');
    Route::post('/earning/leadership_bonus', 'Member\MemberEarningController@leadership_bonus_earning');
    Route::post('/earning/royalty_bonus', 'Member\MemberEarningController@royalty_bonus_earning');
    Route::post('/earning/board', 'Member\MemberEarningController@board');
    Route::post('/earning/monoline', 'Member\MemberEarningController@monoline');
    Route::post('/earning/pass_up', 'Member\MemberEarningController@pass_up');
    Route::post('/earning/leveling_bonus', 'Member\MemberEarningController@leveling_bonus');
    Route::post('/earning/unilevel_or_earning', 'Member\MemberEarningController@unilevel_or_earning');
    Route::post('/earning/universal_pool_bonus', 'Member\MemberEarningController@universal_pool_bonus');
    Route::post('/earning/share_link', 'Member\MemberEarningController@share_link');
    Route::post('/earning/captcha', 'Member\MemberEarningController@captcha_earning');
    Route::post('/cart/get_front_cart', 'Member\MemberProductController@get_front_cart');
    Route::post('/cart/get_items', 'Member\MemberProductController@get_cart_items');
    Route::post('/cart/get_location', 'Member\MemberProductController@get_location');
    Route::post('/member/get_level_item', 'Member\MemberProductController@get_level_item');
    Route::post('/earning/get_dynamic_breakdown', 'Member\MemberEarningController@get_dynamic_breakdown');
    Route::post('/earning/personal_cashback', 'Member\MemberEarningController@personal_cashback');
    Route::post('/earning/retailer_commission', 'Member\MemberEarningController@retailer_commission');
    Route::post('/earning/share_link_v2', 'Member\MemberEarningController@share_link_v2');
    Route::post('/earning/product_share_link', 'Member\MemberEarningController@product_share_link');
    Route::post('/earning/overriding_commission', 'Member\MemberEarningController@overriding_commission');
    Route::post('/earning/product_direct_referral', 'Member\MemberEarningController@product_direct_referral');
    Route::post('/earning/direct_personal_cashback', 'Member\MemberEarningController@direct_personal_cashback');
    Route::post('/earning/product_personal_cashback', 'Member\MemberEarningController@product_personal_cashback');
    Route::post('/earning/team_sales_bonus', 'Member\MemberEarningController@team_sales_bonus');
    Route::post('/earning/retailer_override', 'Member\MemberEarningController@retailer_override');
    Route::post('/earning/reverse_pass_up', 'Member\MemberEarningController@reverse_pass_up');
    Route::post('/earning/achievers_rank', 'Member\MemberEarningController@achievers_rank');
    Route::post('/earning/dropshipping_bonus', 'Member\MemberEarningController@dropshipping_bonus');
    Route::post('/earning/welcome_bonus_earning', 'Member\MemberEarningController@welcome_bonus_earning');
    Route::post('/earning/unilevel_matrix_bonus', 'Member\MemberEarningController@unilevel_matrix_bonus');
    Route::post('/earning/get_matrix_per_level_details', 'Member\MemberEarningController@get_matrix_per_level_details');
    Route::post('/earning/reward_points_earning', 'Member\MemberEarningController@reward_points_earning');
    Route::post('/earning/prime_refund_earning', 'Member\MemberEarningController@prime_refund_earning');
    Route::post('/earning/incentive_earning', 'Member\MemberEarningController@incentive_earning');
    Route::post('/earning/milestone_earning', 'Member\MemberEarningController@milestone_earning');
    Route::post('/earning/milestone_points', 'Member\MemberEarningController@milestone_points');
    Route::post('/earning/infinity_bonus_earning', 'Member\MemberEarningController@infinity_bonus_earning');
    Route::post('/earning/marketing_support_earning', 'Member\MemberEarningController@marketing_support_earning');
    Route::post('/earning/marketing_support_daily_income', 'Member\MemberEarningController@marketing_support_daily_income');
    Route::post('/earning/leaders_support_earning', 'Member\MemberEarningController@leaders_support_earning');

    // CHECKOUT
    Route::post('/checkout', 'Member\MemberProductController@checkout');
    Route::post('/simple_checkout', 'Member\MemberProductController@simpleCheckout');
    Route::post('/get_branch', 'Member\MemberProductController@get_branch');
    Route::post('/getbranch_ecom', 'Member\MemberProductController@getbranch_ecom');
    Route::post('/get_delivery_charge', 'Member\MemberProductController@get_delivery_charge');
    Route::post('/get_payment_method', 'Member\MemberProductController@get_payment_method');
    Route::post('/dragonpay_ServiceCharged', 'Member\MemberProductController@dragonpay_ServiceCharged');
    Route::post('/get_voucher', 'Member\MemberProductController@get_voucher');
    Route::post('/record_item', 'Member\MemberProductController@record_item');
    Route::post('/check_pending_transaction', 'Member\MemberProductController@check_pending_transaction');
    Route::post('/place_order', 'Member\MemberProductController@place_order');
    Route::post('/check_wallet', 'Member\MemberProductController@check_wallet');
    Route::post('/checkout_v2', 'Member\MemberProductController@checkout_v2');
    Route::post('/cancel_order', 'Member\MemberProductController@cancel_order');
    Route::post('/continue_to_shop', 'Member\MemberProductController@continue_to_shop');
    Route::post('/shopping/get_category_list', 'Member\MemberProductController@get_category_list');
    Route::post('/shopping/get_subcategory_list', 'Member\MemberProductController@get_subcategory_list');
    Route::post('/shopping/get_first_category', 'Member\MemberProductController@get_first_category');

    Route::post('/slot/add_slot', 'Member\MemberController@add_slot');
    Route::post('/slot/slot_preview', 'Member\MemberController@slot_preview');
    Route::post('/slot/bulk_slot_preview', 'Member\MemberController@bulk_slot_preview');
    Route::post('/slot/bulk_trans_slot_preview', 'Member\MemberController@bulk_trans_slot_preview');
    Route::post('/slot/add_slot_with_register', 'Member\MemberController@add_slot_with_register');
    Route::post('/check_unplaced_slot', 'Member\MemberSlotController@get_unplaced_slot');
    Route::post('/check_unplaced_downline_slot', 'Member\MemberSlotController@get_unplaced_downline_slot');
    Route::post('/current_slot', 'Member\MemberController@current_slot');
    Route::post('/place_own_slot', 'Member\MemberSlotController@place_own_slot');
    Route::post('/slot_preview_place_own_downline', 'Member\MemberController@slot_preview_place_own_downline');
    Route::post('/place_downline_slot', 'Member\MemberSlotController@place_downline_slot');
    Route::post('/place_downline_slot_other_info', 'Member\MemberSlotController@place_downline_slot_other_info');
    Route::post('/get_unactivated_slot', 'Member\MemberSlotController@get_unactivated_slot');
    Route::post('/transfer_slot', 'Member\MemberSlotController@transfer_slot');

    /* CASH IN */
    Route::post('/cashin/record_cash_in', 'Member\MemberCashInController@record_cash_in');
    Route::post('/cashout/record_cash_out', 'Member\MemberCashOutController@record_cash_out');
    Route::post('/cashout/get_slot_wallet', 'Member\MemberCashOutController@get_slot_wallet');
    Route::post('/cashout/check_if_initial_payout', 'Member\MemberCashOutController@check_if_initial_payout');

    /*MEMBER SETTINGS*/

    Route::post('/settings/get_user_info', 'Member\MemberSettingsController@get_user_info');
    Route::post('/settings/get_user_add_ons_info', 'Member\MemberSettingsController@get_user_add_ons_info');
    Route::post('/settings/update_user_info', 'Member\MemberSettingsController@update_user_info');
    Route::post('/settings/get_addresses', 'Member\MemberSettingsController@get_addresses');
    Route::post('/settings/add_addresses', 'Member\MemberSettingsController@add_addresses');
    Route::post('/settings/update_address_status', 'Member\MemberSettingsController@update_address_status');
    Route::post('/settings/update_address', 'Member\MemberSettingsController@update_address');
    Route::post('/settings/get_location', 'Member\MemberSettingsController@get_location');
    Route::post('/settings/update_password', 'Member\MemberSettingsController@update_password');
    Route::post('/settings/update_changes', 'Member\MemberSettingsController@update_changes');
    Route::post('/settings/add_tin', 'Member\MemberSettingsController@add_tin');
    Route::post('/settings/edit_tin', 'Member\MemberSettingsController@edit_tin');
    Route::post('/settings/upload_profile', 'Member\MemberSettingsController@upload_profile');
    Route::post('/settings/upload_id', 'Member\MemberSettingsController@upload_id');
    Route::post('/settings/check_address', 'Member\MemberSettingsController@check_address');
    Route::post('/settings/kyc_front_id', 'Member\MemberSettingsController@kyc_front_id');
    Route::post('/settings/kyc_back_id', 'Member\MemberSettingsController@kyc_back_id');
    Route::post('/settings/kyc_selfie_id', 'Member\MemberSettingsController@kyc_selfie_id');
    Route::post('/settings/remove_id', 'Member\MemberSettingsController@remove_id');
    Route::post('/settings/update_beneficiary', 'Member\MemberSettingsController@update_beneficiary');
    Route::post('/settings/close_welcome_bonus_notif', 'Member\MemberSettingsController@close_welcome_bonus_notif');

    /*MEMBER ELOADING*/
    Route::post('/member_eloading/get_product_list', 'Member\MemberEloadingController@get_product_list');
    Route::post('/member_eloading/eloading_submit', 'Member\MemberEloadingController@eloading_submit');
    Route::post('/member_eloading/get_wallet', 'Member\MemberEloadingController@get_wallet');
    Route::post('/member_eloading/get_eload_settings', 'Member\MemberEloadingController@get_eload_settings');
    Route::post('/member_eloading/search', 'Member\MemberEloadingController@search');

    /*MEMBER SPONSOR*/
    Route::post('/member_sponsor/get_sponsor_list', 'Member\MemberSponsorController@get_sponsor_list');
    Route::post('/member_sponsor/activate_slot', 'Member\MemberSponsorController@activate_slot');

    Route::post('/member/rate_item', 'Member\MemberProductController@rate_item');
    Route::post('/member/user_search', 'Member\MemberController@user_search');
    Route::post('/member/select_user', 'Member\MemberController@select_user');
    Route::post('/member/transfer_code', 'Member\MemberController@transfer_code');
    Route::post('/member/transfer_check_details', 'Member\MemberController@transfer_check_detail');
    Route::post('/member/get_showing_settings', 'Member\MemberController@get_showing_settings');

    Route::post('/member/search_product', 'Member\MemberProductController@search_product');

    /*MEMBER VIDEO*/
    Route::post('/member/get_video', 'Member\MemberVideoController@get_video');
    Route::post('/member/get_settings', 'Member\MemberVideoController@get_settings');
    Route::post('/member/get_recent', 'Member\MemberVideoController@get_recent');
    Route::post('/member/video_reward', 'Member\MemberVideoController@video_reward');
    Route::post('/member/play_recent', 'Member\MemberVideoController@play_recent');

    /*MEMBER SURVEY*/
    Route::post('/member/survey_init', 'Member\MemberSurveyController@survey_init');
    Route::post('/member/survey_question', 'Member\MemberSurveyController@survey_question');
    Route::post('/member/survey_answer', 'Member\MemberSurveyController@survey_answer');

    /*MEMBER EBOOKS*/
    Route::post('/member/ebooks/get_data', 'Member\MemberEbooksController@get_data');
    Route::post('/member/ebooks/get_category', 'Member\MemberEbooksController@get_category');
    Route::post('/member/ebooks/get_subcategory', 'Member\MemberEbooksController@get_subcategory');
    Route::post('/member/ebooks/get_selected_tools', 'Member\MemberEbooksController@get_selected_tools');
    Route::post('/member/ebooks/download_from_s3', 'Member\MemberEbooksController@download_from_s3');
    
    /*PRODUCT REPLICATED LINK*/
    Route::post('/member/get_product_link', 'Member\MemberProductController@get_product_link');

    /*LIVE STREAM*/
    Route::post('member/live/streaming/get_live_data', 'Member\MemberLiveStreamingController@get_live_data');

    /*LEADERBOARD*/
    Route::post('member/leaderboard/load_settings', 'Member\MemberLeaderBoardController@load_settings');
    Route::post('member/leaderboard/load_topearner', 'Member\MemberLeaderBoardController@load_topearner');
    Route::post('member/leaderboard/load_birthday_list', 'Member\MemberLeaderBoardController@load_birthday_list');
    Route::post('member/leaderboard/load_announcement', 'Member\MemberLeaderBoardController@load_announcement');

    // GET PLAN STATUS
    Route::post('/plan/get_status', 'Admin\AdminPlanController@get');

    // MEMBER ADD TO CART
    Route::post('/cart/add_to_cart', 'Member\MemberOrderController@addToCart');
    Route::post('/cart/simple_add_to_cart', 'Member\MemberOrderController@simpleAddToCart');
    Route::post('/cart/get_cart_items', 'Member\MemberOrderController@getCartItems');
    Route::post('/cart/update_cart_item', 'Member\MemberOrderController@updateCartItem');
    Route::post('/cart/delete_item', 'Member\MemberOrderController@deleteItem');
    Route::post('/cart/delete_all_item', 'Member\MemberOrderController@deleteAllItem');

    // ACHIEVERS RANK // Created By: Centy - 10-27-2023
    Route::post('/achievers_rank/achievers_rank', 'Member\MemberAchieversRankController@achievers_rank');
    Route::post('/achievers_rank/achievers_claimed', 'Member\MemberAchieversRankController@achievers_claimed');
});

Route::group(['middleware' => ['auth:api', 'cashier']], function () {
    Route::post('/cashier/item_list', 'Cashier\CashierItemController@get_item_list');
    Route::post('/cashier/check_invoice', 'Cashier\CashierItemController@check_invoice');
    Route::post('/cashier/claim_code_list', 'Cashier\CashierItemController@get_claim_code_list');
    Route::post('/cashier/select_claim_code', 'Cashier\CashierItemController@select_claim_code');
    Route::post('/cashier/customer_list', 'Cashier\CashierItemController@get_customer_list');
    Route::post('/cashier/checkout_items', 'Cashier\CashierItemController@checkout');
    Route::post('/cashier/select', 'Cashier\CashierItemController@select');
    Route::post('/cashier/load_receipt', 'Cashier\CashierItemController@load_receipt');
    Route::post('/cashier/select_slot', 'Cashier\CashierItemController@select_slot');
    Route::post('/cashier/adjust_discount', 'Cashier\CashierItemController@adjust_discount');
    Route::post('/cashier/get_user', 'Cashier\CashierItemController@get_user');
    Route::post('/cashier/get_access', 'Cashier\CashierItemController@get_access');
    Route::post('/cashier/process_sale', 'Cashier\CashierItemController@process_sale');
    Route::post('/cashier/update_claim_code', 'Cashier\CashierItemController@update_claim_code');
    Route::post('/cashier/load_sales_report', 'Cashier\CashierItemController@load_sales_report');
    Route::post('/cashier/get_addresses', 'Cashier\CashierItemController@get_addresses');
    Route::post('/cashier/country/get', 'Admin\AdminCountryController@get');
    Route::post('/cashier/add_member', 'Cashier\CashierItemController@register_member');
    Route::post('/cashier/load_membership_kit', 'Cashier\CashierItemController@load_membership_kit');
    Route::post('/cashier/select_for_slot_creation', 'Cashier\CashierItemController@select_for_slot_creation');
    Route::post('/cashier/create_slot', 'Cashier\CashierItemController@create_slot');
    Route::post('/cashier/check_password', 'Cashier\CashierItemController@check_password');
    Route::post('/cashier/check_stocks', 'Cashier\CashierItemController@check_stocks');
    Route::post('/cashier/list_of_codes', 'Cashier\CashierItemController@list_of_codes');
    Route::post('/cashier/recount_inventory', 'Cashier\CashierItemController@recount_inventory');
    Route::post('/cashier/get_payment_type', 'Cashier\CashierItemController@get_payment_type');
    Route::post('/cashier/upline_preview', 'Cashier\CashierItemController@upline_preview');
    Route::post('/cashier/submit_placement', 'Cashier\CashierItemController@submit_placement');

    Route::post('/cashier/get_cashier_info', 'Cashier\CashierController@get_cashier_info');
    Route::post('/cashier/update_cashier_info', 'Cashier\CashierController@update_cashier_info');
    Route::post('/cashier/load_company_info', 'Cashier\CashierController@load_company_info');
    Route::post('/cashier/sales_receipt', 'Cashier\CashierController@sales_receipt');

    Route::post('/cashier/get_receipt_details', 'Cashier\CashierController@get_receipt_details');

});

Route::get('/client_secret', 'SecretController@get');
Route::post('/get_country', "RegisterController@get_country");
Route::post('/get_register_settings', "RegisterController@get_register_settings");
Route::post('/new_register', "RegisterController@new_register");
Route::post('/member/check_credentials', "RegisterController@check_credentials");
Route::post('/new_register_check', "RegisterController@new_register_check");

/* DEALERS REGISTER */
Route::post('/check_dealers_code', "RegisterController@check_dealers_code");

Route::get('/test_sched', "TestController@process_payout");

Route::post('/slot/check', 'RegisterController@slot_check');
Route::post('/check_sponsor', 'RegisterController@check_sponsor');

/* Captcha */
Route::post('/recaptcha/link', 'ApiController@recaptcha_link');
Route::post('/recaptcha/main', 'ApiController@main');

// BANNER
Route::post('/load_banner', 'Member\MemberDashboardController@load_banner');

 // PRODUCTS
Route::post('/landing/get_all_products',                  'Member\MemberDashboardController@get_all_products');
Route::post('/landing/getProduct',                        'Member\MemberDashboardController@get_products');
Route::post('/landing/get_new_arrivals',                  'Member\MemberDashboardController@get_new_arrivals');
Route::post('/landing/getProduct_info',                   'Member\MemberDashboardController@getProduct_info');
Route::post('/landing/item_list',                         'Member\MemberDashboardController@item_list');
Route::post('/landing/get_category_list',                 'Member\MemberDashboardController@get_category_list');
Route::post('/landing/getCategory',                       'Member\MemberDashboardController@getCategory');
Route::post('/landing/load_landing_package',              'Member\MemberDashboardController@load_landing_package');
Route::post('/landing/get_cart_items',              'Member\MemberDashboardController@get_cart_items');
Route::post('/landing/get_location',              'Member\MemberDashboardController@get_location');
Route::post('/landing/dropshipping_payment_method',              'Member\MemberDashboardController@dropshipping_payment_method');
Route::post('/landing/checkout_orders',              'Member\MemberDashboardController@checkout_orders');
Route::post('/landing/get_delivery_charge',              'Member\MemberDashboardController@get_delivery_charge');
Route::post('/landing/submit_contact',              'Member\MemberDashboardController@submit_contact');


// PRODUCT REFERRAL

Route::post('/slot/check_referral', 'ProductShareLinkController@check_referral');

// test
Route::post('/member/getDigest', 'DragonPayController@getDigest');
// Route::post('/member/getResponse',                  'DragonPayController@getResponse');
Route::post('/store/check_store_link', 'ProductShareLinkController@check_store_link');

// FORGOT PASSWORD
Route::post('/send_mail', 'ForgotPasswordController@send_mail');
Route::post('/create_new_pass', 'ForgotPasswordController@create_new_pass');
Route::post('/check_timeout', 'ForgotPasswordController@check_timeout');
Route::post('/OTP_check', 'ForgotPasswordController@OTP_check');

// ACTIVATE ACCOUNT
Route::post('/verify_account', 'AccountActivationController@verify_account');

//Check order
Route::get('/orders/latest', 'Member\MemberOrderController@latestOrder');

