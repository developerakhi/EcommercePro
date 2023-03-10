<?php

namespace App\Http\Controllers;

use App\Models\Catagory;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\SendEmailNotification;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;


class AdminController extends Controller
{
    public function view_catagory(){

        if(Auth::id()){

            $data = Catagory::all();
            return view('admin.catagory', compact('data'));
        }
        else {
            return redirect('login');
        }
    }
    public function add_catagory(Request $request){
        $data = new Catagory;
        $data->catagory_name=$request->catagory;
        $data->save();

        return redirect()->back()->with('message', 'Catagory Added Successfully');
    }
    public function delete_catagory($id){
        $data = Catagory::find($id);
        $data->delete();

        return redirect()->back()->with('message', 'Catagory Deleted Successfully');
    }

    public function view_product(){
        $catagory = Catagory::all();

        return view('admin.product', compact('catagory'));
    }

    public function add_product(Request $request){
        $product = new Product;
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->discount_price = $request->discount_price;
        $product->catagory = $request->catagory;

        $image = $request->image;
        $imagename = time() . '.' . $image->getClientOriginalExtension();
        $request->image->move('product', $imagename);

        $product->image = $imagename;

        $product->save();

        return redirect()->back()->with('message', 'Product Added Successfully');
    }

    public function show_product(){
        $product = Product::all();

        return view('admin.show_product', compact('product'));
    }

    public function delete_product($id){
        $product = Product::find($id);
        $product->delete();

        return redirect()->back()->with('message', 'Product Deleted Successfully');
    }

    public function update_product($id){
        $product = Product::find($id);
        $catagory = Catagory::all();

        return view('admin.update_product', compact('product', 'catagory'));
    }

    public function update_product_confirm(Request $request, $id){

        if(Auth::id()){
            $product = Product::find($id);

        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->discount_price = $request->discount_price;
        $product->catagory = $request->catagory;

        
        $image = $request->image;

        if($image){
            $imagename = time() . '.' . $image->getClientOriginalExtension();
            $request->image->move('product', $imagename);
            $product->image = $imagename;
        }
        $product->save();

        return redirect()->back()->with('message', 'Product Updated Successfully');
        }
        else {
            return redirect('login');
        }

        

    }

    public function order(){

        $order = Order::all();
        return view('admin.order', compact('order'));
    }

    public function delivered($id){

        $order = Order::find($id);

        $order->delivery_status = "Delivered";

        $order->payment_status = "Paid";

        $order->save();

        return redirect()->back();
    }

    public function print_pdf($id){
        $order = Order::find($id);
        $pdf = PDF::loadView('admin.pdf', compact('order'));

        return $pdf->download('order_details');
    }

    public function send_email($id){

        $order = Order::find($id);

        return view('admin.email_info', compact('order'));
    }

    public function send_user_email(Request $request, $id){

        $order = Order::find($id);

        $details = [

            'greeting' => $request->greeting,
            'firstline' => $request->firstline,
            'body' => $request->body,
            'button' => $request->button,
            'url' => $request->url,
            'lastline' => $request->lastline

        ];

        Notification::send($order, new SendEmailNotification($details));

        return redirect()->back();
    }

    public function searchdata(Request $request){

        $searchText = $request->search;
        $order = Order::where('name', 'LIKE', "%$searchText%")->orWhere('phone', 'LIKE', "%$searchText%")->orWhere('product_title', 'LIKE', "%$searchText%")->get();
        return view('admin.order', compact('order'));
    }
}
