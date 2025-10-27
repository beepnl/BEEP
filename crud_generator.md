Laravel CRUD generator
https://github.com/appzcoder/crud-generator/blob/master/doc/configuration.md

# SensorDefinition
php artisan crud:generate SensorDefinition -—fields='zero_value#float#nullable; unit_per_value#float#nullable; input_measurement_id#integer#unsigned#nullable; output_measurement_id#integer#unsigned#nullable; sensor_id#integer#unsigned' --relationships='input_measurement_id#belongsTo#Measurement::class; output_measurement_id#belongsTo#Measurement::class; sensor#belongsTo#Sensor::class' --form-helper=html

php artisan crud:api SensorDefinition -—fields='zero_value#float#nullable; unit_per_value#float#nullable; input_measurement_id#integer#unsigned#nullable; output_measurement_id#integer#unsigned#nullable; sensor_id#integer#unsigned' --relationships='input_measurement_id#belongsTo#Measurement::class; output_measurement_id#belongsTo#Measurement::class; sensor#belongsTo#Sensor::class' --controller-namespace=Api

# Image
php artisan crud:generate Image -—fields='filename#string#required; image_url#string#required; thumb_url#string; description#string; type#string; height#number; width#number; size_kb#number; date#timestamp; user_id#integer#unsigned; hive_id#integer#unsigned; category_id#integer#unsigned; checklist_id#integer#unsigned' --relationships=‘user#belongsTo#User::class; hive#belongsTo#Hive::class; category#belongsTo#Category::class; checklist#belongsTo#Checklist::class' --form-helper=html

php artisan crud:api Image --fields='filename#string#required; image_url#string#required; thumb_url#string; description#string; type#string; height#number; width#number; size_kb#number; date#timestamp; user_id#integer#unsigned; hive_id#integer#unsigned; category_id#integer#unsigned; checklist_id#integer#unsigned' --relationships='user#belongsTo#User::class; category#belongsTo#Category::class; hive#belongsTo#Hive::class; checklist#belongsTo#Checklist::class' --controller-namespace=Api

# Research
php artisan crud:generate Research --fields='name#string#required; url#string; image#file; description#string; type#string; institution#string; type_of_data_used#string; start_date#timestamp; end_date#timestamp; checklist_id#integer#unsigned' --relationships='checklist#hasOne#Checklist::class' --form-helper=html

php artisan crud:api Research --fields='name#string#required; url#string; image#file; description#string; type#string; institution#string; type_of_data_used#string; start_date#timestamp; end_date#timestamp; checklist_id#integer#unsigned' --controller-namespace=Api

# Inspection
php artisan crud:generate Inspections --fields='notes#text#nullable; impression#integer#nullable; attention#boolean#nullable; created_at#timestamp#useCurrent;' --relationships='user#belongsTo#Class:User' --form-helper=html --route=no
php artisan crud:generate InspectionItems --fields='value#string#nullable; inspection_id#integer#unsigned; category_id#integer#unsigned' --relationships='inspection#belongsTo#Inspection::class; category#hasOne#Category::class' --form-helper=html

# Measurement
php artisan crud:generate Measurement --fields="abbreviation#string; physical_quantity_id#integer#unsigned; show_in_charts#boolean; chart_group#integer#unsigned; min_value#float" --relationships='physical_quantity#hasOne#PhysicalQuantity::class' --form-helper=html

php artisan crud:controller MeasurementController --crud-name=measurement --model-name=Measurement --force
php artisan crud:view measurement --fields='abbreviation#string; physical_quantity_id#integer#unsigned; show_in_charts#boolean; chart_group#integer#unsigned' --form-helper=html --validations='abbreviation#required; physical_quantity_id#required'


# PhysicalQuantity
php artisan crud:controller PostsController --crud-name=posts --model-name=Post --view-path="directory" --route-group=admin
php artisan crud:controller PhysicalQuantityController --crud-name=physicalquantity --model-name=PhysicalQuantity  --pagination=1000 --force
php artisan crud:view posts --fields="title#string; body#text" --view-path="directory" --route-group=admin --form-helper=html
php artisan crud:view physicalquantity --fields="name#string; unit#string; abbreviation#string" --form-helper=html --validations="name#required; unit#required"

# BeeRace
php artisan crud:controller BeeRaceController --crud-name=beerace --model-name=BeeRace --pagination=1000 --force
php artisan crud:view beerace --fields="name#string; type#string; synonyms#string; continents#string" --form-helper=html --validations="name#required; type#required"

# Categoryinputs
php artisan crud:controller CategoryInputsController --crud-name=categoryinputs --model-name=CategoryInput --pagination=1000 --force
php artisan crud:view categoryinputs --fields="name#string; type#string; min#number; max#number; decimals#number; icon#string" --form-helper=html  --validations="name#required; type#required"

# HiveTytpes
php artisan crud:controller HiveTypeController --crud-name=hivetype --model-name=HiveType --pagination=1000 --force
php artisan crud:view hivetype --fields="name#string; type#string; image#string; continents#string; info_url#string" --form-helper=html --validations="name#required; type#required"

# Categories
php artisan crud:controller CategoryController --crud-name=categories --model-name=Category --pagination=1000
php artisan crud:view categories --fields="name#string; type#string; options#string; parent_id#number" --form-helper=html  --validations="name#required; type#required"

# Checklists
php artisan crud:model Checklist --fillable="['name', 'type', 'description']"
php artisan crud:controller ChecklistController --crud-name=checklists --model-name=Checklist --pagination=1000
php artisan crud:view checklists --fields="name#string; type#string; description#string" --form-helper=html

# Laguages
php artisan crud:controller LanguageController --crud-name=languages --model-name=Language --pagination=1000
php artisan crud:view languages --fields="name#string; name_english#string; icon#string; abbreviation#string" --form-helper=html  --validations="name#required; name_english#required; abbreviation#required"

# Permissions
php artisan crud:controller PermissionController --crud-name=permissions --model-name=Permission --pagination=1000
php artisan crud:view permissions --fields="name#string; display_name#string; description#string" --form-helper=html --validations="name#required; display_name#required; description#required"

# LabCode
php artisan crud:generate SampleCode --fields="sample_code#string#unique; sample_note#text; sample_date#timestamp; test_result#text; test#text; test_date#timestamp; test_lab_name#text; hive_id#integer#unsigned; queen_id#integer#unsigned; user_id#integer#unsigned;" --relationships='hive#belongsTo#Hive::class; queen#belongsTo#Queen::class; user#belongsTo#User::class;' --form-helper=html --validations="sample_code#required; hive_id#required"

# FlashLog
php artisan crud:generate FlashLog --fields="user_id#integer#unsigned; device_id#integer#unsigned; hive_id#integer#unsigned#nullable; log_messages#integer#unsigned#nullable; log_saved#boolean; log_parsed#boolean; log_has_timestamps#boolean; bytes_received#integer#unsigned#nullable; log_file#string#nullable; log_file_stripped#string#nullable; log_file_parsed#string#nullable" --relationships='hive#belongsTo#Hive::class; device#belongsTo#Device::class; user#belongsTo#User::class;' --form-helper=html

# HiveTags
php artisan crud:generate HiveTags --fields="user_id#integer#unsigned; tag#string; hive_id#integer#unsigned#nullable; action_id#integer#unsigned#nullable; router_link#json#nullable" --relationships='hive#belongsTo#Hive::class; user#belongsTo#User::class;' --form-helper=html --validations="tag#required; user_id#required"
php artisan crud:api-controller Api\\HiveTagsController --crud-name=hive_tags --model-name=Models\\HiveTag

# DashboardGroup
php artisan crud:generate DashboardGroup --fields="user_id#integer#unsigned; code#string; name#string#nullable; hive_ids#json#nullable; speed#integer#unsigned; interval#string; show_inspections#boolean; show_all#boolean; hide_measurements#boolean; logo_url#string#nullable" --relationships='hive_ids#belongsTo#Hive::class; user#belongsTo#User::class;' --form-helper=html --validations="code#required; hive_ids#required; user_id#required; interval#required; speed#required"
php artisan crud:api-controller Api\\DashboardGroupController --crud-name=dgroup --model-name=Models\\DashboardGroup

# ChecklistSvg
php artisan crud:generate ChecklistSvg --fields="user_id#integer#unsigned; checklist_id#integer#unsigned; name#string#nullable; svg#mediumtext#nullable; pages#integer#unsigned#nullable; last_print#datetime#nullable" --relationships='checklist_id#belongsTo#Checklist::class; user#belongsTo#User::class;' --form-helper=html --validations="user_id#required; checklist_id#required"
php artisan crud:api-controller Api\\ChecklistSvgController --crud-name=checklist_svg --model-name=Models\\ChecklistSvg

# AlertRuleFormula
php artisan crud:generate AlertRuleFormula --fields="alert_rule_id#integer#unsigned; measurement_id#integer#unsigned; calculation#string; comparator#string; comparison#string; logical#string#nullable; period_minutes#integer#nullable; threshold_value#float" --relationships='alert_rule_id#belongsTo#AlertRule::class; measurement_id#belongsTo#Measurement::class' --form-helper=html --validations="alert_rule_id#required; measurement_id#required; calculation#required; comparator#required; comparison#required; threshold_value#required"
php artisan crud:api-controller Api\\AlertRuleFormulaController --crud-name=alert_rule_formula --model-name=Models\\AlertRuleFormula

# CalculationModel
php artisan crud:generate CalculationModel --fields="name#string; measurement_id#integer#unsigned; data_measurement_id#integer#unsigned; data_interval#string#nullable; data_relative_interval#boolean#true; data_interval_index#integer#0;  "


