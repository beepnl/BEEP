<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleCode extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sample_codes';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['sample_code', 'sample_note', 'sample_date', 'test_result', 'test', 'test_date', 'test_lab_name', 'user_id', 'hive_id', 'queen_id'];

    public function hive()
    {
        return $this->belongsTo(Hive::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function queen()
    {
        return $this->belongsTo(Queen::class);
    }
    
    public static function generate_code()
    {
        $code = null;
        while ($code == null || SampleCode::where('sample_code', $code)->count() > 0)
            $code = SampleCode::readable_random_string();

        return $code;
    }

    public static  function readable_random_string($length = 8)
    {  
        $string  = '';
        $letters = array(
            'a', 'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 
            'n', 'p', 'r', 's', 't', 'u', 'x', 'y', 'z'
        );  

        // Seed it
        srand((double) microtime() * 1000000);

        $cnt = count($letters);

        for ($i = 0; $i < $length; $i++)
        {
            $string .= $letters[rand(0,$cnt-1)];
        }

        return strtoupper($string);
    }
}
