<?php
/*
Plugin Name: Dunkin Drink Menu Calculator
Description: Dunkin Drink Menu Nutrition Calculator helps users calculate Calories, Sugar, Caffeine, Fat, Protein, Carbs, and Servings for menu items, with full and single-nutrient calculators via simple shortcodes.
Version: 1.0
Author: Muhammad Usman Gujjar
*/

// Create database table on activation
register_activation_hook(__FILE__, 'dd_calorie_create_table');
function dd_calorie_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dd_menu_items';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        category varchar(255) NOT NULL,
        item_name varchar(255) NOT NULL,
        calories int NOT NULL,
        sugar int DEFAULT 0,
        caffeine int DEFAULT 0,
        fat int DEFAULT 0,
        protein int DEFAULT 0,
        carbs int DEFAULT 0,
        servings int DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Admin menu
add_action('admin_menu', 'dd_calorie_admin_menu');
function dd_calorie_admin_menu() {
    add_menu_page('Dunkin Menu', 'Dunkin Menu', 'manage_options', 'dd-menu', 'dd_calorie_admin_page');
}

// Admin page content
function dd_calorie_admin_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'dd_menu_items';

    if (isset($_POST['add_item'])) {
        $wpdb->insert($table, [
            'category' => sanitize_text_field($_POST['category']),
            'item_name' => sanitize_text_field($_POST['item_name']),
            'calories' => intval($_POST['calories']),
            'sugar' => intval($_POST['sugar']),
            'caffeine' => intval($_POST['caffeine']),
            'fat' => intval($_POST['fat']),
            'protein' => intval($_POST['protein']),
            'carbs' => intval($_POST['carbs']),
            'servings' => intval($_POST['servings'])
        ]);
    }

    if (isset($_POST['update_item'])) {
        $wpdb->update($table, [
            'category' => sanitize_text_field($_POST['category']),
            'item_name' => sanitize_text_field($_POST['item_name']),
            'calories' => intval($_POST['calories']),
            'sugar' => intval($_POST['sugar']),
            'caffeine' => intval($_POST['caffeine']),
            'fat' => intval($_POST['fat']),
            'protein' => intval($_POST['protein']),
            'carbs' => intval($_POST['carbs']),
            'servings' => intval($_POST['servings'])
        ], ['id' => intval($_POST['item_id'])]);
    }

    if (isset($_POST['delete_item'])) {
        $wpdb->delete($table, ['id' => intval($_POST['item_id'])]);
    }

    $items = $wpdb->get_results("SELECT * FROM $table ORDER BY category ASC, item_name ASC");
    ?>

    <div class="wrap">
        <h1>Dunkin Donuts Menu Items</h1>
        <hr>
        <h2>Add New Item</h2>
        <form method="post">
            <input type="text" name="category" placeholder="Category" required>
            <input type="text" name="item_name" placeholder="Item Name" required>
            <input type="number" name="calories" placeholder="Calories" required>
            <input type="number" name="sugar" placeholder="Sugar (g)">
            <input type="number" name="caffeine" placeholder="Caffeine (mg)">
            <input type="number" name="fat" placeholder="Fat (g)">
            <input type="number" name="protein" placeholder="Protein (g)">
            <input type="number" name="carbs" placeholder="Carbs (g)">
            <input type="number" name="servings" placeholder="Servings" >
            <button type="submit" name="add_item" class="button button-primary">Add Item</button>
        </form>

        <hr>
        <h2 style="margin-top: 30px;">All Menu Items</h2>
        <div class="responsive-table">
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Sr. No.</th><th>Category</th><th>Name</th><th>Calories</th>
                        <th>Sugar</th><th>Caffeine</th><th>Fat</th><th>Protein</th><th>Carbs</th><th>Servings</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    $sr_no = 1;
                    foreach ($items as $item): 
                ?>
                    <tr>
                        <form method="post">
                            <td><?php echo $sr_no++; ?></td>
                            <td><input type="text" name="category" value="<?php echo esc_attr($item->category); ?>"></td>
                            <td><input type="text" name="item_name" value="<?php echo esc_attr($item->item_name); ?>"></td>
                            <td><input type="number" name="calories" value="<?php echo $item->calories; ?>"></td>
                            <td><input type="number" name="sugar" value="<?php echo $item->sugar; ?>"></td>
                            <td><input type="number" name="caffeine" value="<?php echo $item->caffeine; ?>"></td>
                            <td><input type="number" name="fat" value="<?php echo $item->fat; ?>"></td>
                            <td><input type="number" name="protein" value="<?php echo $item->protein; ?>"></td>
                            <td><input type="number" name="carbs" value="<?php echo $item->carbs; ?>"></td>
                            <td><input type="number" name="servings" value="<?php echo $item->servings; ?>"></td>
                            <td>
                                <input type="hidden" name="item_id" value="<?php echo $item->id; ?>">
                                <button type="submit" name="update_item" class="button">Update</button> <br><hr>
                                <button type="submit" name="delete_item" class="button button-danger" onclick="return confirm('Are you sure?')" style="background-color:red; color:white;">Delete</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 30px;">
            <hr>
            <h3>Note:</h3>
            <p>
            All Type Calculator (Calories, Sugar, Caffeine, Fat, Protein, Carbs, Servings)<br>
            Category -> 'Name of Menu' <br>
            Value -> 'Calories, Sugar, Caffeine, Fat, Protein, Carbs, Servings' <br><br>
                <b>Short Code: </b><br> <br>
            All Type ->    [dunkin_nutrition_calculator] <br><hr>
            [dunkin_calorie_calculator category="Drinks" value="Calories"]<br>
            [dunkin_calorie_calculator category="Drinks" value="Sugar"]<br>
            [dunkin_calorie_calculator category="Drinks" value="Caffeine"]<br>
            [dunkin_calorie_calculator category="Drinks" value="Protein"]<br>
            [dunkin_calorie_calculator category="Drinks" value="Carbs"]<br>
            [dunkin_calorie_calculator category="Bakery" value="Servings"]<br><hr>
            </p>
        </div>
    </div>

    <style>
        .responsive-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .responsive-table th, .responsive-table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .responsive-table th {
            background-color: #f4f4f4;
        }
        .responsive-table {
            overflow-x: auto;
        }
    </style>
<?php }

add_shortcode('dunkin_calorie_calculator', 'dd_calorie_shortcode');
function dd_calorie_shortcode($atts) {
    global $wpdb;
    $table = $wpdb->prefix . 'dd_menu_items';

    $atts = shortcode_atts([
        'category' => '',
        'value' => 'Calories',
    ], $atts);

    $category = sanitize_text_field($atts['category']);
    $value_type = sanitize_text_field($atts['value']);

    if (!$category) return '<p><em>Please provide a category in the shortcode.</em></p>';

    $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE category = %s ORDER BY item_name", $category));
    if (empty($items)) return '<p><em>No items found for category: ' . esc_html($category) . '</em></p>';

    ob_start();
    ?>
    <style>
        .calculate-button:hover {
            background-color: #FF8E1B;
            cursor: pointer;
        }
    </style>
    <div id="dunkin-calorie-calculator" style="max-width: 500px; padding: 25px; border: 1px solid #ccc; border-radius: 10px;">
        <h2 style="text-align: center;">Dunkin’ Donuts <?php echo esc_html($value_type); ?> Calculator</h2>
        <label><strong>Select Menu Item:</strong></label>
        <select id="menu-item" style="width: 100%; padding: 8px; margin: 10px;">
            <?php foreach ($items as $index => $item): ?>
                <option value="<?php echo $index; ?>"><?php echo esc_html($item->item_name); ?></option>
            <?php endforeach; ?>
        </select>
        <label><strong>Quantity:</strong></label>
        <input type="number" id="quantity" value="1" min="1" style="width: 100%; padding: 8px; margin:10px;" />
        <button onclick="calculateSelected()" class="calculate-button" style="width: 100%; padding: 10px; background-color: #A2592D; color: white; border: none; border-radius: 5px;">
            Calculate <?php echo esc_html($value_type); ?>
        </button>
        <div id="results" style="margin-top: 15px;">
            <strong>Total <?php echo esc_html($value_type); ?>:</strong> <span id="value-result">0</span>
        </div>
    </div>
    <script>
        const items = <?php echo json_encode($items); ?>;
        const valueType = "<?php echo esc_js(strtolower($value_type)); ?>";

        function calculateSelected() {
            const index = document.getElementById("menu-item").value;
            const qty = parseInt(document.getElementById("quantity").value);
            const item = items[index];

            let result = 0;
            switch (valueType) {
                case 'calories': result = item.calories; break;
                case 'sugar': result = item.sugar; break;
                case 'caffeine': result = item.caffeine; break;
                case 'fat': result = item.fat; break;
                case 'protein': result = item.protein; break;
                case 'carbs': result = item.carbs; break;
                case 'servings': result = item.servings; break;
            }
            document.getElementById("value-result").innerText = result * qty;
        }
    </script>
    <?php
    return ob_get_clean();
}



add_shortcode('dunkin_nutrition_calculator', 'dd_full_calorie_calculator');
function dd_full_calorie_calculator() {
    global $wpdb;
    $items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dd_menu_items");

    ob_start(); ?>
    <style>
        .nutrition-container {
            padding: 20px;
            /* background: #fff6ef; */
            border-radius: 10px;
            max-width: 100%;
            margin: auto;
            box-sizing: border-box;
        }
        .nutrition-card {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .nutrition-card h2 {
            margin-top: 0;
            color: #d34c3d;
        }
        .nutrition-field {
            display: flex;
            flex-direction: column;
            flex: 1 1 200px;
        }
        .nutrition-field label {
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 0.95em;
        }
        .nutrition-field select,
        .nutrition-field input {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1em;
        }
        .nutrition-inputs {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }
        .nutrition-add-button {
            padding: 10px 20px;
            background-color: #A2592D;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            white-space: nowrap;
        }
        .nutrition-add-button:hover {
            background-color: #8a471f;
        }
        .nutrition-table-container {
            overflow-x: auto;
        }
        .nutrition-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .nutrition-table th,
        .nutrition-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            min-width: 80px;
        }
        .summary p {
            margin: 4px 0;
        }
        .reset-btn {
            background: #d9534f;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .nutrition-inputs {
                flex-direction: column;
            }
            .nutrition-add-button {
                width: 100%;
            }
        }
    </style>

    <div class="nutrition-container">
        <!-- Input Card -->
        <div class="nutrition-card">
            <div class="nutrition-inputs">
                <div class="nutrition-field">
                    <label for="itemSelect">Choose Your Item:</label>
                    <select id="itemSelect">
                        <?php foreach ($items as $item): ?>
                            <option value="<?php echo esc_attr(json_encode($item)); ?>">
                                <?php echo esc_html($item->item_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="nutrition-field">
                    <label for="servingCount">Number of Servings:</label>
                    <input type="number" id="servingCount" value="1" min="1" placeholder="Servings">
                </div>
                <button class="nutrition-add-button" onclick="addItemToTable()">Add Item</button>
            </div>
        </div>

        <!-- Selected Items Table Card -->
        <div class="nutrition-card">
            <h2>Selected Items</h2>
            <div class="nutrition-table-container">
                <table class="nutrition-table" id="itemTable">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Servings</th>
                            <th>Calories</th>
                            <th>Fat</th>
                            <th>Protein</th>
                            <th>Sugar</th>
                            <th>Carbs</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Nutrition Summary Card -->
        <div class="nutrition-card summary" id="nutritionSummary">
            <h2>Total Nutritional Information</h2>
            <p>Calories: 0 cal</p>
            <p>Fat: 0g</p>
            <p>Protein: 0g</p>
            <p>Sugar: 0g</p>
            <p>Carbs: 0g</p>
            <button class="reset-btn" onclick="resetNutrition()">Reset</button>
        </div>
    </div>

    <script>
        const tableBody = document.querySelector("#itemTable tbody");
        const summary = document.getElementById("nutritionSummary");
        let total = { calories: 0, fat: 0, protein: 0, sugar: 0, carbs: 0 };

        function addItemToTable() {
            const selectedData = JSON.parse(document.getElementById("itemSelect").value);
            const servings = parseInt(document.getElementById("servingCount").value) || 1;

            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${selectedData.item_name}</td>
                <td>${servings}</td>
                <td>${selectedData.calories * servings}</td>
                <td>${selectedData.fat * servings}</td>
                <td>${selectedData.protein * servings}</td>
                <td>${selectedData.sugar * servings}</td>
                <td>${selectedData.carbs * servings}</td>
                <td><button class="reset-btn" onclick="removeItem(this, ${selectedData.calories * servings}, ${selectedData.fat * servings}, ${selectedData.protein * servings}, ${selectedData.sugar * servings}, ${selectedData.carbs * servings})">Remove</button></td>
            `;
            tableBody.appendChild(row);

            total.calories += selectedData.calories * servings;
            total.fat += selectedData.fat * servings;
            total.protein += selectedData.protein * servings;
            total.sugar += selectedData.sugar * servings;
            total.carbs += selectedData.carbs * servings;

            updateSummary();
        }

        function updateSummary() {
            summary.innerHTML = `
                <h2>Total Nutritional Information</h2>
                <p>Calories: ${total.calories} cal</p>
                <p>Fat: ${total.fat}g</p>
                <p>Protein: ${total.protein}g</p>
                <p>Sugar: ${total.sugar}g</p>
                <p>Carbs: ${total.carbs}g</p>
                <button class="reset-btn" onclick="resetNutrition()">Reset</button>
            `;
        }

        function removeItem(btn, cals, fat, protein, sugar, carbs) {
            btn.closest("tr").remove();
            total.calories -= cals;
            total.fat -= fat;
            total.protein -= protein;
            total.sugar -= sugar;
            total.carbs -= carbs;
            updateSummary();
        }

        function resetNutrition() {
            tableBody.innerHTML = '';
            total = { calories: 0, fat: 0, protein: 0, sugar: 0, carbs: 0 };
            updateSummary();
        }
    </script>
    <?php
    return ob_get_clean();
}



