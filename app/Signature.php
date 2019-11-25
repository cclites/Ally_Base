<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Signature
 *
 * @property int $id
 * @property int $signable_id
 * @property string $signable_type
 * @property mixed $content
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $signable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereSignableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereSignableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Signature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Signature extends AuditableModel
{
    protected $guarded = ['id'];

    /**
     * Get all of the owning signable models.
     */
    public function signable()
    {
        return $this->morphTo();
    }
    
    public static function attachToModel(Model $model, $content, $type = null )
    {
        if ($content) {
            return Signature::create([
                'signable_id'   => $model->getKey(),
                'signable_type' => $model->getMorphClass(),
                'content'       => $content,
                'meta_type'     => $type
            ]);
        }
        return null;
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        // Fake signature that says "Test"
        return [
            'content' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 800 300" width="800" height="300"><path d="M 127.222,136.000 C 130.722,136.000 130.722,136.000 134.222,136.000" stroke-width="5.165" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 134.222,136.000 C 143.722,136.000 143.728,136.243 153.222,136.000" stroke-width="3.109" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 153.222,136.000 C 163.228,135.743 163.217,135.303 173.222,135.000" stroke-width="2.655" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 173.222,135.000 C 179.717,134.803 179.760,135.554 186.222,135.000" stroke-width="3.007" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 186.222,135.000 C 197.260,134.054 197.192,133.226 208.222,132.000" stroke-width="2.519" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 208.222,132.000 C 219.692,130.726 219.712,130.767 231.222,130.000" stroke-width="2.481" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 177.222,138.000 C 178.222,142.000 178.507,141.949 179.222,146.000" stroke-width="5.139" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 179.222,146.000 C 180.007,150.449 179.957,150.487 180.222,155.000" stroke-width="4.014" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 180.222,155.000 C 180.457,158.987 180.222,159.000 180.222,163.000" stroke-width="3.929" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 180.222,163.000 C 180.222,171.500 180.222,171.500 180.222,180.000" stroke-width="3.075" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 180.222,180.000 C 180.222,190.000 180.222,190.000 180.222,200.000" stroke-width="2.714" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 223.222,180.000 C 227.722,180.000 227.842,180.796 232.222,180.000" stroke-width="5.205" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 232.222,180.000 C 238.842,178.796 238.842,178.320 245.222,176.000" stroke-width="3.474" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 245.222,176.000 C 249.842,174.320 249.777,174.117 254.222,172.000" stroke-width="3.535" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 254.222,172.000 C 260.277,169.117 260.297,169.137 266.222,166.000" stroke-width="3.891" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 266.222,166.000 C 268.797,164.637 271.222,164.668 271.222,163.000" stroke-width="4.719" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 271.222,163.000 C 271.222,161.168 268.938,160.630 266.222,159.000" stroke-width="5.134" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 266.222,159.000 C 263.938,157.630 263.844,157.492 261.222,157.000" stroke-width="5.181" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 261.222,157.000 C 255.844,155.992 255.736,156.204 250.222,156.000" stroke-width="4.489" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 250.222,156.000 C 242.236,155.704 242.078,155.252 234.222,156.000" stroke-width="4.003" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 234.222,156.000 C 231.578,156.252 231.574,156.717 229.222,158.000" stroke-width="4.776" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 229.222,158.000 C 226.074,159.717 226.263,160.065 223.222,162.000" stroke-width="4.109" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 223.222,162.000 C 220.763,163.565 220.591,163.308 218.222,165.000" stroke-width="4.119" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 218.222,165.000 C 213.591,168.308 213.393,168.177 209.222,172.000" stroke-width="4.162" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 209.222,172.000 C 207.393,173.677 206.847,173.708 206.222,176.000" stroke-width="4.862" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 206.222,176.000 C 205.347,179.208 205.933,179.529 206.222,183.000" stroke-width="5.021" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 206.222,183.000 C 206.433,185.529 206.037,185.867 207.222,188.000" stroke-width="5.073" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 207.222,188.000 C 208.537,190.367 208.812,190.623 211.222,192.000" stroke-width="5.059" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 211.222,192.000 C 215.812,194.623 216.104,194.384 221.222,196.000" stroke-width="4.749" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 221.222,196.000 C 225.604,197.384 225.718,197.021 230.222,198.000" stroke-width="3.920" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 230.222,198.000 C 237.218,199.521 237.217,199.525 244.222,201.000" stroke-width="3.922" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 244.222,201.000 C 246.717,201.525 246.689,201.831 249.222,202.000" stroke-width="4.554" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 249.222,202.000 C 254.189,202.331 254.222,202.000 259.222,202.000" stroke-width="4.385" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 259.222,202.000 C 265.722,202.000 265.858,202.707 272.222,202.000" stroke-width="4.134" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 331.222,164.000 C 328.222,162.500 328.339,162.199 325.222,161.000" stroke-width="5.395" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 325.222,161.000 C 321.839,159.699 321.781,159.712 318.222,159.000" stroke-width="5.055" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 318.222,159.000 C 314.281,158.212 314.234,158.309 310.222,158.000" stroke-width="4.905" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 310.222,158.000 C 307.734,157.809 307.571,157.359 305.222,158.000" stroke-width="4.891" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 305.222,158.000 C 302.071,158.859 301.630,158.860 299.222,161.000" stroke-width="5.109" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 299.222,161.000 C 297.130,162.860 294.624,164.402 296.222,166.000" stroke-width="5.059" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 296.222,166.000 C 301.124,170.902 304.051,170.498 312.222,174.000" stroke-width="5.072" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 312.222,174.000 C 314.551,174.998 314.847,174.136 317.222,175.000" stroke-width="4.944" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 317.222,175.000 C 320.347,176.136 320.134,176.713 323.222,178.000" stroke-width="5.056" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 323.222,178.000 C 326.134,179.213 326.311,178.787 329.222,180.000" stroke-width="4.346" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 329.222,180.000 C 332.311,181.287 332.357,181.281 335.222,183.000" stroke-width="4.571" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 335.222,183.000 C 337.357,184.281 339.222,184.500 339.222,186.000" stroke-width="5.149" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 339.222,186.000 C 339.222,187.500 337.174,187.439 335.222,189.000" stroke-width="5.131" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 335.222,189.000 C 332.174,191.439 332.575,192.476 329.222,194.000" stroke-width="5.065" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 329.222,194.000 C 327.075,194.976 326.710,193.809 324.222,194.000" stroke-width="5.175" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 324.222,194.000 C 320.210,194.309 320.241,194.809 316.222,195.000" stroke-width="4.719" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 316.222,195.000 C 309.741,195.309 309.623,194.147 303.222,195.000" stroke-width="3.531" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 303.222,195.000 C 294.623,196.147 294.789,197.572 286.222,199.000" stroke-width="2.897" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 390.222,127.000 C 389.722,131.500 389.544,131.490 389.222,136.000" stroke-width="5.197" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 389.222,136.000 C 389.044,138.490 389.044,138.510 389.222,141.000" stroke-width="4.524" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 389.222,141.000 C 389.544,145.510 389.940,145.488 390.222,150.000" stroke-width="4.425" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 390.222,150.000 C 390.440,153.488 389.763,153.557 390.222,157.000" stroke-width="4.054" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 390.222,157.000 C 390.763,161.057 391.600,160.954 392.222,165.000" stroke-width="3.864" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 392.222,165.000 C 392.600,167.454 392.222,167.500 392.222,170.000" stroke-width="4.190" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 392.222,170.000 C 392.222,174.000 392.222,174.000 392.222,178.000" stroke-width="4.420" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 392.222,178.000 C 392.222,181.000 392.222,181.000 392.222,184.000" stroke-width="4.832" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 392.222,184.000 C 392.222,187.500 392.222,187.500 392.222,191.000" stroke-width="4.714" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 392.222,191.000 C 392.222,194.000 392.222,194.000 392.222,197.000" stroke-width="5.172" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 360.222,151.000 C 363.222,152.000 363.143,152.487 366.222,153.000" stroke-width="5.350" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 366.222,153.000 C 369.143,153.487 369.222,153.000 372.222,153.000" stroke-width="4.922" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 372.222,153.000 C 374.722,153.000 374.747,153.248 377.222,153.000" stroke-width="4.505" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 377.222,153.000 C 379.747,152.748 379.695,152.230 382.222,152.000" stroke-width="4.756" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 382.222,152.000 C 385.195,151.730 385.229,152.166 388.222,152.000" stroke-width="4.774" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 388.222,152.000 C 394.229,151.666 394.349,152.036 400.222,151.000" stroke-width="4.603" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 400.222,151.000 C 402.849,150.536 402.654,149.770 405.222,149.000" stroke-width="5.024" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 405.222,149.000 C 407.654,148.270 407.790,148.730 410.222,148.000" stroke-width="5.197" stroke="black" fill="none" stroke-linecap="round"></path><path d="M 410.222,148.000 C 412.790,147.230 412.629,146.519 415.222,146.000" stroke-width="5.267" stroke="black" fill="none" stroke-linecap="round"></path></svg>',
        ];
    }
}
