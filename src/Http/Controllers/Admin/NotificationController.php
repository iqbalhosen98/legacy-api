<?php

namespace Mrpath\API\Http\Controllers\Admin;

use Mrpath\Admin\Http\Controllers\Controller;
use Mrpath\API\DataGrids\PushNotificationDataGrid;
use Mrpath\API\Repositories\NotificationRepository;
use Mrpath\API\Helpers\SendNotification;
use Mrpath\Category\Repositories\CategoryRepository;
use Mrpath\Core\Repositories\ChannelRepository;
use Mrpath\Product\Repositories\ProductRepository;

class NotificationController extends Controller
{
    /**
     * Contains route related configuration.
     *
     * @var array
     */
    protected $_config;

    /**
     * Notification repository instance.
     *
     * @var \Mrpath\API\Repositories\NotificationRepository
     */
    protected $notificationRepository;

    /**
     * SendNotification helper instance.
     *
     * @var \Mrpath\API\Helpers\SendNotification
     */
    protected $sendNotification;

    /**
     * Channel repository instance.
     *
     * @var \Mrpath\Core\Repositories\ChannelRepository
     */
    protected $channelRepository;

    /**
     * Product repository instance.
     *
     * @var \Mrpath\Product\Repositories\ProductRepository
     */
    protected $productRepository;

    /**
     * Category repository instance.
     *
     * @var \Mrpath\Category\Repositories\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Create a new controller instance.
     *
     * @param \Mrpath\API\Repositories\NotificationRepository  $notificationRepository
     * @param \Mrpath\API\Helpers\SendNotification  $sendNotification
     * @param \Mrpath\Core\Repositories\ChannelRepository  $channelRepository
     * @param \Mrpath\Product\Repositories\ProductRepository  $productRepository
     * @param \Mrpath\Category\Repositories\CategoryRepository  $categoryRepository
     */
    public function __construct(
        ChannelRepository $channelRepository,
        NotificationRepository $notificationRepository,
        SendNotification $sendNotification,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    )   {
        $this->_config = request('_config');

        $this->middleware('admin');

        $this->notificationRepository = $notificationRepository;

        $this->sendNotification = $sendNotification;

        $this->channelRepository = $channelRepository;

        $this->productRepository = $productRepository;

        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(PushNotificationDataGrid::class)->toJson();
        }

        return view($this->_config['view']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $channels = $this->channelRepository->get();

        return view($this->_config['view'], compact('channels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'title'     => 'string|required',
            'content'   => 'string|required',
            'image.*'   => 'mimes:jpeg,jpg,bmp,png',
            'type'      => 'required',
            'channels'  => 'required',
            'status'    => 'required'
        ]);
        
        $data = collect(request()->all())->except('_token')->toArray();

        $this->notificationRepository->create($data);

        session()->flash('success', trans('api::app.alert.create-success', ['name' => 'Notification']));

        return redirect()->route('api.notification.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $notification = $this->notificationRepository->findOrFail($id);

        $channels = $this->channelRepository->get();

        return view($this->_config['view'], compact('notification', 'channels'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $this->validate(request(), [
            'title'     => 'string|required',
            'content'   => 'string|required',
            'image.*'   => 'mimes:jpeg,jpg,bmp,png',
            'type'      => 'required',
            'channels'  => 'required',
            'status'    => 'required'
        ]);

        $data = collect(request()->all())->except('_token')->toArray();

        $this->notificationRepository->update($data, $id);

        session()->flash('success', trans('api::app.alert.update-success', ['name' => 'Notification']));

        return redirect()->route('api.notification.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $this->notificationRepository->delete($id);

            session()->flash('success', trans('api::app.alert.delete-success', ['name' => 'Notification']));

            return response()->json(['message' => true], 200);
        } catch(\Exception $e) {
            session()->flash('success', trans('api::app.alert.delete-failed', ['name' => 'Notification']));
        }

        return response()->json(['message' => false], 400);
    }

    /**
     * To mass update the notification.
     *
     * @return \Illuminate\Http\Response
     */
    public function massUpdate()
    {
        $notificationIds = explode(',', request()->input('indexes'));
        $updateOption = request()->input('update-options');

        foreach ($notificationIds as $notificationId) {
            $notification = $this->notificationRepository->find($notificationId);

            $notification->update([
                'status' => $updateOption
            ]);
        }

        session()->flash('success', trans('api::app.alert.update-success', ['name' => 'Notification']));

        return redirect()->back();
    }

    /**
     * To mass delete the notificaton.
     *
     * @return \Illuminate\Http\Response
     */
    public function massDestroy()
    {
        $notificationIds = explode(',', request()->input('indexes'));

        foreach ($notificationIds as $notificationId) {
            $this->notificationRepository->deleteWhere([
                'id' => $notificationId
            ]);
        }

        session()->flash('success', trans('api::app.alert.delete-success', ['name' => 'Notification']));

        return redirect()->back();
    }

    /**
     * To sent the notification to the device.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendNotification($id)
    {
        $data = $this->notificationRepository->find($id);

        $notification = $this->sendNotification->sendGCM($data);

        if (isset($notification->message_id)) {
            session()->flash('success', trans('api::app.alert.sended-successfully', ['name' => 'Notification']));
        } elseif (isset($notification->error)) {
            session()->flash('error', $notification->error);
        }

        return redirect()->back();
    }

    /**
     * To check resource exist in DB.
     *
     * @return \Illuminate\Http\Response
     */
    public function exist()
    {
        $data = request()->all();

        if ( substr_count($data['givenValue'], ' ') > 0) {
            return response()->json(['value' => false, 'message' => 'Product not exist', 'type' => $data['selectedType']],200);
        }

        //product case
        if ($data['selectedType'] == 'product') {
            if ($product = $this->productRepository->find($data['givenValue'])) {

                if (! isset($product->id) || !isset($product->url_key) || ( isset($product->parent_id) && $product->parent_id) ) {
                    return response()->json(['value' => false, 'message' => 'Product not exist', 'type' => 'product'], 200);
                } else {
                    return response()->json(['value' => true], 200);
                }
            } else {
                return response()->json(['value' => false, 'message' => 'Product not exist', 'type' => 'product'], 200);
            }
        }

        //category case
        if ($data['selectedType'] == 'category') {
            if ($this->categoryRepository->find($data['givenValue'])) {
                return response()->json(['value' => true] ,200);
            } else {
                return response()->json(['value' => false, 'message' => 'Category not exist', 'type' => 'category'] ,200);
            }
        }
    }
}
