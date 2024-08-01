<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperatingHours;


class OperatinghoursController extends Controller
{
    //
    public function index(Request $request)
    {
        if(\Auth::user()->type == "Admin")
        {
            $opeatings = OperatingHours::where('created_by',\Auth::user()->id)->get();

            return view('admin.operating_hours.index',compact('opeatings'));
        }
        else{
            return view('403');
        }
    }

    public function create()
    {
        $days = OperatingHours::$days;
        return view('admin.operating_hours.create',compact('days'));
    }

    public function store(Request $request)
    {
        $user = \Auth::user();

        if(\Auth::user()->type == "Admin")
        {
            $validation = [
                'name' => 'required|string|max:255',
                'content' => 'required|string|max:255',

            ];
            $days = $data = [];
            foreach ($request->content as $key => $value) {
                if(array_key_exists($key , $request->days)){
                    $data[$key] = $value;
                }
            }

            $opeating = new OperatingHours();
            $opeating->name = $request->name;
            $opeating->content = json_encode($data);
            $opeating->created_by = \Auth::user()->createId();
            $opeating->save();
            return redirect()->route('operating_hours.index')->with('success', __('OperatingHours created successfully'));
        }
    }

    public function show($id)
    {
        $opeating = OperatingHours::find($id);
        $days = OperatingHours::$days;

        return view('admin.operating_hours.show',compact('opeating','days'));

    }

    public function edit($id)
    {
        $user = \Auth::user();
        if(\Auth::user()->type == "Admin")
        {
            $opeatings = OperatingHours::find($id);
            $days = OperatingHours::$days;
            return view('admin.operating_hours.edit', compact('opeatings','days'));
        }
        else
        {
            return view('403');
        }

    }




    public function update(Request $request, $id)
    {
        // Validate the form data
        $validatedData = $request->validate([
            'name' => 'required|string',
            'content' => 'required|array',
        ]);

        // Retrieve the content data for each day
        $contentData = $validatedData['content'];

        $updatedContent = [];

        // Loop through the content data to get start_time and end_time for each day
        foreach ($contentData as $day => $data) {
            $start_time = $data['start_time'];
            $end_time = $data['end_time'];

            // Store the start_time and end_time in the updated content array
            $updatedContent[$day] = [
                'start_time' => $start_time,
                'end_time' => $end_time,
            ];
        }

        // Find the existing operating hours record
        $operatingHours = OperatingHours::find($id);

        // Update the attributes of the operating hours record
        $operatingHours->name = $request->name;
        $operatingHours->content = json_encode($updatedContent);
        $operatingHours->created_by = \Auth::user()->id;
        $operatingHours->save();

        // Redirect with a success message
        return redirect()->route('operating_hours.index')
            ->with('success', 'Operating hours updated successfully');
    }




    public function destroy($id)
    {
        $user = \Auth::user();
        if(\Auth::user()->type == "Admin")
        {
            $opeatings = OperatingHours::find($id);
            $opeatings->delete();

            return redirect()->back()->with('success', __('OperatingHours deleted successfully'));
        }
        else
        {
            return view('403');
        }
    }

}
