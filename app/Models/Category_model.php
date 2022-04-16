<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class Category_model extends Model
{

    use HasFactory;

    protected $table = "category_schema";
    protected $primaryKey = "id";

    /*
     *  left nav
     */

    static public function fetchlv1()
    {
        return self::query()
                        ->where("id", "<=", 9)
                        ->get()
                        ->toArray();
    }

    static public function fetchlv2v3($typeid = null)
    {
        $result = [];
        $lv2 = self::query()
                ->where('plevel', 'REGEXP', $typeid . '-[1-9][0-9]*$')
                ->orderBy('sorting', 'desc')
                ->get()
                ->toArray();

        if (count($lv2) == 0) {
            return $result;
        }

        foreach ($lv2 as $value) {
            $lv3 = self::query()
                    ->where('plevel', 'REGEXP', $value['plevel'] . '-[1-9][0-9]*$')
                    ->where('display', '=', 'on')
                    ->orderBy('sorting', 'desc')
                    ->get()
                    ->toArray();
            array_push($result, [$value, $lv3]);
        }
        return $result;
    }

    /*
     * end
     */

    /*
     * location
     */

    static public function get_lv1_path($id)
    {
//        \DB::enableQueryLog();
        $result = self::query()
                ->where("id", "=", $id)
                ->get()
                ->toArray();
//        dd(\DB::getQueryLog());
        return $result;
    }

    static public function get_lv2_path($id)
    {
        $loc1 = explode("-", $id);

        $result_lv2 = self::query()
                ->where("plevel", "=", $id)
                ->get()
                ->toArray();

        $result_lv1 = self::query()
                ->where("id", "=", $loc1[0])
                ->get()
                ->toArray();

        return [$result_lv1, $result_lv2];
    }

    static public function get_lv3_path($id)
    {
        $loc1 = explode("-", $id);

        $result_lv3 = self::query()
                ->where("plevel", "=", $id)
                ->get()
                ->toArray();

        $result_lv2 = self::query()
                ->where("plevel", "=", $loc1[0] . "-" . $loc1[1])
                ->get()
                ->toArray();

        $result_lv1 = self::query()
                ->where("id", "=", $loc1[0])
                ->get()
                ->toArray();

        return [$result_lv1, $result_lv2, $result_lv3];
    }

    /*
     * end
     */

    /*
     * table lists
     */

    static public function get_lv1_tablelists($id)
    {
        //方法 1-1
        $paginate_lv1 = self::query()
                ->select(['*', \DB::raw('DATE(date) AS newDate')])
                ->where("plevel", "REGEXP", "^{$id}-[1-9][0-9]*$")
                ->orderByDesc("id")
                ->paginate(10)
                ->onEachSide(2);

        $result_lv1 = $paginate_lv1->toArray();

        return [$result_lv1, $paginate_lv1];

        //方法 1-2
//        $result_lv1 = self::query()
//                ->where("plevel", "REGEXP", "^{$id}-[1-9][0-9]*$")
//                ->get()
//                ->get(["*", \DB::raw('DATE(date) AS newDate')])
//                ->toArray();
        //方法 2
//        $result_lv1 = self::query()
//                ->raw(\DB::select('select *,date(date) AS newDate FROM category_schema where plevel REGEXP "^' . $id . '-[1-9][0-9]*$"'))
//                ->getValue();
//        return $result_lv1;
    }

    static public function get_lv2_tablelists($id)
    {
        $paginate_lv2 = self::query()
                ->select(['*', \DB::raw('DATE(date) AS newDate')])
                ->where("plevel", "REGEXP", "^{$id}-[1-9][0-9]*$")
                ->orderByDesc("id")
                ->paginate(10)
                ->onEachSide(2);

        $result_lv2 = $paginate_lv2->toArray();

        return [$result_lv2, $paginate_lv2];
    }

    /*
     * upadte data
     */

    static public function formUpadte($data)
    {
        $result = \DB::update("UPDATE category_schema SET category=?, sorting=?, display=? WHERE id=?", array($data["category"], $data["sorting"], $data["display"], $data["id"]));
        return $result;
    }

    /*
     * create data
     */

    static public function formCreate($data)
    {
        $result = \DB::insert("INSERT INTO category_schema (plevel,type,category,sorting,display,date) VALUE(?,?,?,?,?,NOW())",
                        [$data["plevel"], $_SESSION['category'], $data["category"], $data["sorting"], $data["display"]]);

        return $result;
    }

    /*
     * delete data
     */

    static public function formDelete()
    {
        $result = \DB::delete("DELETE FROM category_schema WHERE id=?", [session("check.edit.id")]);

        $result = \DB::statement("ALTER TABLE content_schema AUTO_INCREMENT = 1");

        return "success";
    }

    /*
     * order data
     */

    static public function formOrder($data)
    {
        foreach ($data as $value) {
            \DB::update("UPDATE category_schema SET sorting=? WHERE id=?", $value);
        }

        return "success";
    }

    /*
     * 使用content的id取得自身資料
     */

    static public function get_id2data($id)
    {
        $result = \DB::select("SELECT * FROM category_schema WHERE id=?", [$id]);
        return $result;
    }

    /*
     * 取得category的最新plevel
     */

    protected function getlatestPlevel($parent)
    {
        $result = self::query()
                ->select('*')
                ->where("plevel", "REGEXP", "^{$parent}-[1-9][0-9]*$")
                ->get()
                ->toArray();

        return $result;
    }

}
