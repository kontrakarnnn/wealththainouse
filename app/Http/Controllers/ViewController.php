<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\View;
use App\Block;
use App\Http\Controllers\SidebarController;

class ViewController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('view');
    }

	     public function getArrayAlldBlock($currentstruc,$currentid,$notebook){

    $CurrentDivisions = Block::where('id', '=',$currentid )->get();
    $result =$notebook;
    $ChildDivisions = Block::whereIn('under_block',$currentid )->pluck('id');
    foreach ( $ChildDivisions as $Division => $get) {
      $nextblockID[$Division] = $get;
      $arraylength = sizeof($result);
      //$currentid=$currentid;
      $result[$arraylength]  = $nextblockID[$Division];
      $result = $this->getArrayAlldBlock($currentstruc,$nextblockID,$result);
      }

      return $result;
}


public function blockbtu($currentstruc2,$currentid2,$notebook2){

$CurrentDivisions = Block::where('under_block', '=', NULL )->pluck('id');
$result2 =$notebook2;
$ChildDivisions = Block::whereIn('id',$currentid2)->pluck('under_block');
//$ChildDivisions = Block::whereIn('under_block',$currentid2)->pluck('id'); topdown
//  $ChildDivisions = Block::whereIn('id',$currentid)->pluck('under_block');
//  $ChildDivisions = Block::whereIn('id',$CurrentDivisions )->pluck('id');
foreach ( $ChildDivisions as $Division => $get) {
  $nextblockID2[$Division] = $get;
  $arraylength = sizeof($result2);
  //$currentid=$currentid;
  $result2[$arraylength]  = $nextblockID2[$Division];
  $result2 = $this->blockbtu($currentstruc2,$nextblockID2,$result2);
  }

  return $result2;
}







    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

      //sidebar

    $tree = session()->get('tree');
    //sidebar


          $views = DB::table('views as v')
        ->leftJoin('views as vi', 'v.belong_to', '=', 'vi.id')
        ->select('v.*','vi.name as view_name', 'vi.id as view_id')
        ->paginate(50);

        return view('system-mgmt/view/index', ['views' => $views,'tree'=>$tree]);
    }



    public function side()
    {
        $views = View::paginate(5);

        return view('system-mgmt/view/side', ['views' => $views]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      //sidebar

    $tree = session()->get('tree');
    //sidebar




        $views = View::all();
        return view('system-mgmt/view/create',['views' => $views,'tree'=>$tree]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validateInput($request);
         view::create([
            'name' => $request['name'],
            'view_url' => $request['view_url'],
            'belong_to' => $request['belong_to'],
			'add_to_side' => $request['add_to_side'],
      'level' => $request['level'],
			'sub_node' => $request['sub_node'],
      'priority' => $request['priority'],
        ]);

        return redirect('/admin/view');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

      $blocks = View::find($id)->portfolio;
      $views = Vew::paginate(5);
      return view('/view/index', compact(['views','blocks']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

      //sidebar

$tree = session()->get('tree');
//sidebar


        $view =View::find($id);
		$views =View::all();
        // Redirect to department list if updating department wasn't existed
        if ($view == null) {
          $view = View::find($id);
          $data = array(
              'view' => $view
            );
            return redirect ('/admin/view');
        }

        return view('system-mgmt/view/edit', ['view' => $view,'views' => $views,'tree'=>$tree]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $view = View::findOrFail($id);
        $this->validateInput($request);
        $input = [
            'name' => $request['name'],
            'view_url' => $request['view_url'],
            'belong_to' => $request['belong_to'],
			'add_to_side' => $request['add_to_side'],
      'level' => $request['level'],
			'sub_node' => $request['sub_node'],
      'priority' => $request['priority'],
        ];
        View::where('id', $id)
            ->update($input);

        return redirect ('/admin/view');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        View::where('id', $id)->delete();
         return redirect()->back();
    }

    /**
     * Search department from database base on some specific constraints
     *
     * @param  \Illuminate\Http\Request  $request
     *  @return \Illuminate\Http\Response
     */
    public function search(Request $request) {

      //sidebar

    $tree = session()->get('tree');
    //sidebar
        $constraints = [
            'name' => $request['name']
            ];

       $views = $this->doSearchingQuery($constraints);
       return view('system-mgmt/view/index', ['views' => $views, 'searchingVals' => $constraints,'tree'=>$tree]);
    }

    private function doSearchingQuery($constraints) {
        $query = View::query();
        $fields = array_keys($constraints);
        $index = 0;
        foreach ($constraints as $constraint) {
            if ($constraint != null) {
                $query = $query->where( $fields[$index], 'like', '%'.$constraint.'%');
            }

            $index++;
        }
        return $query->paginate(1000);
    }
    private function validateInput($request) {
        $this->validate($request, [
        //'name' => 'unique:views',
        'view_url' => 'required|'
    ]);
    }
}
