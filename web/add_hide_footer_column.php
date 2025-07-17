<?php
/**
 * Temporary script to add hide_footer column to companies table
 * Delete this file after running once
 */

// Include Yii framework
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');
new yii\web\Application($config);

try {
    $db = Yii::$app->db;
    
    // Check if column exists
    $result = $db->createCommand("SHOW COLUMNS FROM jdosa_companies LIKE 'hide_footer'")->queryAll();
    
    if (empty($result)) {
        // Column doesn't exist, add it
        $db->createCommand("ALTER TABLE jdosa_companies ADD COLUMN hide_footer TINYINT(1) DEFAULT 0 COMMENT 'Hide footer text in PDF documents'")->execute();
        echo "✅ hide_footer column added successfully!<br>";
    } else {
        echo "ℹ️ hide_footer column already exists.<br>";
    }
    
    // Verify the column was added
    $verify = $db->createCommand("SHOW COLUMNS FROM jdosa_companies LIKE 'hide_footer'")->queryAll();
    if (!empty($verify)) {
        echo "✅ Verification: hide_footer column is present in the database.<br>";
        echo "<strong>Column details:</strong><br>";
        echo "<pre>" . print_r($verify[0], true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><strong>You can now delete this file: /web/add_hide_footer_column.php</strong>";
?>