<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\model\GoodsModel;


class GoodsController extends Controller
{

    use HasResourceActions;
    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('商品管理')
            ->description('商品列表')
            ->body($this->grid());
    }
    protected function grid()
    {
        $grid = new Grid(new GoodsModel());

        $grid->model()->orderBy("goods_id","desc");
        $grid->goods_id('商品ID');
        $grid->goods_name('商品名称');
        $grid->goods_price("商品价格");
        $grid->goods_num("库存");
        $grid->created_at('添加时间');
        return $grid;
    }




    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('商品管理')
            ->description('编辑')
            ->body($this->form()->edit($id));

    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {


        return $content
            ->header('商品管理')
            ->description('添加')
            ->body($this->form());

    }
    public function show($id, Content $content)
    {
        return $content
            ->header('商品列表')
            ->description('商品详情')
            ->body($this->detail($id));
    }
    protected function detail($id)
    {
        $show = new Show(GoodsModel::findOrFail($id));

        $show->goods_id("商品ID");
        $show->goods_name("商品名称");
        $show->goods_price("商品价格");
        return $show;
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new GoodsModel());

        $form->display('goods_id', '商品ID');
        $form->text('goods_name', '商品名称');
        $form->number('goods_num', '库存');
        $form->currency("goods_price","价格")->symbol("￥");
        return $form;
    }
}
