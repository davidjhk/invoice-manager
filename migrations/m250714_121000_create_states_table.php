<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%states}}`.
 */
class m250714_121000_create_states_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%states}}', [
            'id' => $this->primaryKey(),
            'state_code' => $this->string(2)->notNull()->comment('US state abbreviation'),
            'state_name' => $this->string(50)->notNull()->comment('Full US state name'),
            'country_code' => $this->string(2)->notNull()->defaultValue('US')->comment('Country code (default US)'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Add unique constraint for state_code and country_code combination
        $this->createIndex('idx-states-state_code-country', '{{%states}}', ['state_code', 'country_code'], true);
        
        // Add index for country_code
        $this->createIndex('idx-states-country_code', '{{%states}}', 'country_code');
        
        // Insert US states data
        $this->batchInsert('{{%states}}', ['state_code', 'state_name', 'country_code'], [
            ['AL', 'Alabama', 'US'],
            ['AK', 'Alaska', 'US'],
            ['AZ', 'Arizona', 'US'],
            ['AR', 'Arkansas', 'US'],
            ['CA', 'California', 'US'],
            ['CO', 'Colorado', 'US'],
            ['CT', 'Connecticut', 'US'],
            ['DE', 'Delaware', 'US'],
            ['FL', 'Florida', 'US'],
            ['GA', 'Georgia', 'US'],
            ['HI', 'Hawaii', 'US'],
            ['ID', 'Idaho', 'US'],
            ['IL', 'Illinois', 'US'],
            ['IN', 'Indiana', 'US'],
            ['IA', 'Iowa', 'US'],
            ['KS', 'Kansas', 'US'],
            ['KY', 'Kentucky', 'US'],
            ['LA', 'Louisiana', 'US'],
            ['ME', 'Maine', 'US'],
            ['MD', 'Maryland', 'US'],
            ['MA', 'Massachusetts', 'US'],
            ['MI', 'Michigan', 'US'],
            ['MN', 'Minnesota', 'US'],
            ['MS', 'Mississippi', 'US'],
            ['MO', 'Missouri', 'US'],
            ['MT', 'Montana', 'US'],
            ['NE', 'Nebraska', 'US'],
            ['NV', 'Nevada', 'US'],
            ['NH', 'New Hampshire', 'US'],
            ['NJ', 'New Jersey', 'US'],
            ['NM', 'New Mexico', 'US'],
            ['NY', 'New York', 'US'],
            ['NC', 'North Carolina', 'US'],
            ['ND', 'North Dakota', 'US'],
            ['OH', 'Ohio', 'US'],
            ['OK', 'Oklahoma', 'US'],
            ['OR', 'Oregon', 'US'],
            ['PA', 'Pennsylvania', 'US'],
            ['RI', 'Rhode Island', 'US'],
            ['SC', 'South Carolina', 'US'],
            ['SD', 'South Dakota', 'US'],
            ['TN', 'Tennessee', 'US'],
            ['TX', 'Texas', 'US'],
            ['UT', 'Utah', 'US'],
            ['VT', 'Vermont', 'US'],
            ['VA', 'Virginia', 'US'],
            ['WA', 'Washington', 'US'],
            ['WV', 'West Virginia', 'US'],
            ['WI', 'Wisconsin', 'US'],
            ['WY', 'Wyoming', 'US'],
            // US territories and federal district
            ['DC', 'District of Columbia', 'US'],
            ['AS', 'American Samoa', 'US'],
            ['GU', 'Guam', 'US'],
            ['MP', 'Northern Mariana Islands', 'US'],
            ['PR', 'Puerto Rico', 'US'],
            ['VI', 'U.S. Virgin Islands', 'US'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%states}}');
    }
}