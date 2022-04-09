<?php

namespace Modules\Entity\Http\Controllers\admin;

use Dotenv\Parser\Entry;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Entity\Entities\Entity;
use Modules\Entity\Entities\EntityGroup;
use Modules\Entity\Http\Requests\Admin\EntityRequest;

class EntityController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['permission:admin-manage-entities']);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(EntityRequest $request)
    {
        $data = Entity::with('entityGroup')->Filter($request)->sortable()->paginate(10);
        return view('entity::admin.index', compact('data'));
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function indexGroup(EntityRequest $request)
    {
        return Response::success('',EntityGroup::orderBy('title')->get());
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $groups = EntityGroup::get();
        return view('entity::admin.create_edit',compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(EntityRequest $request)
    {
        $data = $request->all();
        $data['is_active'] = isset($data['is_active'])?1:0;
        $entity = Entity::createEntity($data);

        return redirect(route('admin.entities.index'))->with('success', __('admin.successfully added',['key' => __('entity::admin.entity'), 'value' => $entity->name]));
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function storeGroup(EntityRequest $request)
    {
        $data = $request->all();
        $entityGroup = EntityGroup::create(['title' => $data['title']]);
        return Response::success(__('admin.successfully added', ['key' => __('entity::admin.group'), 'value' => $entityGroup->name]),[]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        // return view('entity::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($entity)
    {
        $groups = EntityGroup::get();
        $data = Entity::find($entity);
        return view('entity::admin.create_edit',compact('data', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(EntityRequest $request, Entity $entity)
    {
        $data = $request->all();
        $data['is_active'] = isset($data['is_active'])?1:0;
        $entity->update($data);

        return redirect(route('admin.entities.index'))->with('success', __('admin.successfully edited',['key' => __('entity::admin.entity'), 'value' => $entity->name]));
    }

    /**
     * Show the form for duplicating the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function duplicate(EntityRequest $request, $entity)
    {
        $newData = $request->all();
        $newData['is_active'] = isset($newData['is_active'])?1:0;
        $dup = Entity::createEntity($newData);

        return redirect(route('admin.entities.index'))->with('success', __('admin.successfully added',['key' => __('entity::admin.entity'), 'value' => $dup->title]));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Entity $entity)
    {
        $entity->deleteEntity($entity);
        return redirect(route('admin.entities.index'))->with('success', __('admin.successfully deleted',['key' => __('entity::admin.entity'), 'value' => $entity->name]));
    }


    /**
     * load entity data manager view
     *
     * @param Entity $entity
     * @return void
     */
    public function entityDataManager(Entity $entity)
    {
        return view('entity::admin/entityDataManager', compact('entity'));
    }

    public function entityDataManagerApi(Entity $entity){

        $data = [];
        $entityFields = [[
            'field' => 'id',
            'label' => "#",
            'searchable' => false
        ]];
        foreach($entity->entityFields as $key => $value)
            $entityFields[] = [
                'field' => $value->name,
                'label' => $value->title."\r\n".$value->name,
                'searchable' => true
            ];

        $data = DB::select("select * from " . $entity->getTableName() . "");


        return Response::success(__('admin.successfully fetched'), [
            'entity' => $entity,
            'columns' => $entityFields,
            'data' => $data,
            'total' => count($data)
        ]);
    }


}
