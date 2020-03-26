<?php
namespace common\models;

use Yii;

/**
 * This is the model class for table "ip".
 *
 * @property string $ip_start
 * @property string $ip_end
 * @property string $ip_start_num
 * @property string $ip_end_num
 * @property string $continent
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $district
 * @property string $isp
 * @property string $area_code
 * @property string $country_english
 * @property string $country_code
 * @property string $longitude
 * @property string $latitude
 */
class Ip extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip_start_num', 'ip_end_num'], 'integer'],
            [['ip_start', 'ip_end', 'continent', 'country', 'province', 'city', 'district', 'isp', 'area_code', 'country_english', 'country_code', 'longitude', 'latitude'], 'string', 'max' => 45]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ip_start' => 'Ip Start',
            'ip_end' => 'Ip End',
            'ip_start_num' => 'Ip Start Num',
            'ip_end_num' => 'Ip End Num',
            'continent' => 'Continent',
            'country' => 'Country',
            'province' => 'Province',
            'city' => 'City',
            'district' => 'District',
            'isp' => 'Isp',
            'area_code' => 'Area Code',
            'country_english' => 'Country English',
            'country_code' => 'Country Code',
            'longitude' => 'Longitude',
            'latitude' => 'Latitude',
        ];
    }

}
