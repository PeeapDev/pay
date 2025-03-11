<?php

namespace App\Http\Controllers\Admin;

use App\Constants\LanguageConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\FrontendHeaderSection;
use App\Models\Admin\Language;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\Response;
use App\Models\Admin\FrontendHeaderSectionFaq;
use App\Models\Admin\FrontendHeaderSectionPage;
use Exception;
use Illuminate\Http\RedirectResponse;

class FrontendHeaderSectionController extends Controller
{
    protected $languages;

    public function __construct()
    {
        $this->languages = Language::whereNot('code',LanguageConst::NOT_REMOVABLE)->get();
    }
    //===============================Header Section =======================================
        public function index($slug){
            $page_title = ucfirst($slug) ." "."Section";
            $data = FrontendHeaderSection::where('type', $slug)->paginate(20);
            $languages = $this->languages;
            return view('admin.sections.header-section.index',compact(
                'page_title',
                'data',
                'slug',
                'languages'
            ));
        }
        public function create($slug){
            $page_title = __("Create")." ".ucfirst($slug) ." ".__("Section");
            $languages = $this->languages;
            return view('admin.sections.header-section.create',compact(
                'page_title',
                'slug',
                'languages'
            ));
        }
        public function store(Request $request, $slug){

            $title      = ['title'      => 'required|string|max:200'];
            $icon       = ['icon'       => 'required|string|max:200'];
            $sub_title  = ['sub_title'  => 'required|string'];
            $type = $slug;

            $title_data['language']     = $this->contentValidate($request,$title);
            $icon_data['language']      = $this->contentValidate($request,$icon);
            $sub_data['language']       = $this->contentValidate($request,$sub_title);

            $data['title']          = $title_data;
            $data['slug']           = $request->page ?? null;
            $data['type']           = $type;
            $data['icon']           = $icon_data;
            $data['sub_title']      = $sub_data;
            $data['last_edit_by']   = Auth::id();

            try {
                FrontendHeaderSection::create($data);
            } catch (Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            return back()->with(['success' => [__("Section item added successfully")]]);
        }
        public function edit($slug,$id){
            $page_title = __("editS")." ".ucfirst($slug) ." ".__("Section");
            $data = FrontendHeaderSection::where('type', $slug)->where('id',$id)->first();
            if(!$data) return back()->with(['error' => [__("Page record not found in our system.")]]);
            $languages = $this->languages;
            return view('admin.sections.header-section.edit',compact(
                'page_title',
                'data',
                'slug',
                'languages'
            ));
        }
        public function update(Request $request,$slug,$id){
            $section = FrontendHeaderSection::where('type', $slug)->where('id',$id)->first();
            if(!$section) return back()->with(['error' => [__("Page record not found in our system.")]]);

            $title      = ['title'      => 'required|string|max:200'];
            $icon       = ['icon'       => 'required|string|max:200'];
            $sub_title  = ['sub_title'  => 'required|string'];
            $type = $slug;

            $title_data['language']     = $this->contentValidate($request,$title);
            $icon_data['language']      = $this->contentValidate($request,$icon);
            $sub_data['language']       = $this->contentValidate($request,$sub_title);

            $data['title']          = $title_data;
            $data['slug']           = $request->page ?? null;
            $data['type']           = $type;
            $data['icon']           = $icon_data;
            $data['sub_title']      = $sub_data;
            $data['last_edit_by']   = Auth::id();

            try {
                $section->fill($data)->save();
            } catch (Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
            return back()->with(['success' => [__("Section updated successfully!")]]);
        }
        public function statusUpdate(Request $request) {

            $validator = Validator::make($request->all(),[
                'status'                    => 'required|boolean',
                'data_target'               => 'required|string',
            ]);

            if ($validator->stopOnFirstFailure()->fails()) {
                $error = ['error' => $validator->errors()];
                return Response::error($error,null,400);
            }

            $validated = $validator->safe()->all();
            $id = $validated['data_target'];

            $setup_page = FrontendHeaderSection::findOrFail($id);

            if(!$setup_page) {
                $error = ['error' => [__("Page record not found in our system.")]];
                return Response::error($error,null,404);
            }
            try{
                $setup_page->update([
                    'status' => ($validated['status'] == true) ? false : true,
                ]);
            }catch(Exception $e) {
                $error = ['error' => [__("Something went wrong! Please try again.")]];
                return Response::error($error,null,500);
            }
            $success = ['success' => [__("Page status updated successfully!")]];

            return Response::success($success,null,200);
        }
        public function delete(Request $request) {
            $request->validate([
                'target'    => 'required|string',
            ]);

            $setup_page = FrontendHeaderSection::findOrFail($request->target);

            try{
                $setup_page->delete();
            }catch(Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            return back()->with(['success' => [__("Page deleted successfully!")]]);
        }
    //======================================Header Section====================================
    //======================================Header Page Section===============================
        public function pageIndex($type,$parent_id,$slug){
            $page_title = __("Setup Section");
            $parent = FrontendHeaderSection::where('type',$type)->where('id',$parent_id)->first();
            if(!$parent) return back()->with(['error' => [__("Page record not found in our system.")]]);
            $data = FrontendHeaderSectionPage::where("type",$type)->where('parent_id',$parent_id)->first();
            $languages = $this->languages;
            return view('admin.sections.header-section.pages.index',compact(
                'page_title',
                'parent',
                'data',
                'type',
                'languages'
            ));
        }
        public function pageUpdate(Request $request,$type,$parent_id){

            $basic_field_name = [
                'heading'               => "required|string",
                'sub_heading'           => "required|string",
                'process_step_title'    => "required|string",
                'button_name'           => "required|string",
                'button_link'           => "required|string",
                'step_title'            => "required|string",
                'step_sub_title'        => "required|string",
            ];
            $parent = FrontendHeaderSection::where('type',$type)->where('id',$parent_id)->first();
            if(!$parent) return back()->with(['error' => [__("Page record not found in our system.")]]);

            $section = FrontendHeaderSectionPage::where("type",$type)->where('parent_id',$parent_id)->first();

            if($section != null) {
                $data = json_decode(json_encode($section->value),true);
            }else {
                $data = [];
            }

            $data['images']['section_image'] = $section->value->images->section_image ?? "";
            if($request->hasFile("section_image")) {
                $data['images']['section_image']      = $this->imageValidate($request,"section_image",$section->value->images->section_image ?? null);
            }

            $data['images']['step_image'] = $section->value->images->step_image ?? "";
            if($request->hasFile("step_image")) {
                $data['images']['step_image']      = $this->imageValidate($request,"step_image",$section->value->images->step_image ?? null);
            }

            $data['language']  = $this->contentValidate($request,$basic_field_name);
            $update_data['value']           = $data;
            $update_data['type']            = $type;
            $update_data['parent_id']       = $parent_id;
            $update_data['last_edit_by']    = Auth::id();

            try{
                $existingRecord = FrontendHeaderSectionPage::where("type",$type)->where('parent_id',$parent_id)->first();
                if ($existingRecord) {
                    $existingRecord->update($update_data);
                } else {
                    FrontendHeaderSectionPage::create($update_data);
                }
            }catch(Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            return back()->with(['success' => [__("Section updated successfully!")]]);

        }
        public function pageItemStore(Request $request,$type,$parent_id){
            $basic_field_name = [
                'name'     => "required|string",
            ];
            $language_wise_data = $this->contentValidate($request,$basic_field_name,"step-add");
            if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
            $section = FrontendHeaderSectionPage::where("type",$type)->where('parent_id',$parent_id)->first();
            if($section != null) {
                $section_data = json_decode(json_encode($section->value),true);
            }else {
                $section_data = [];
            }
            $unique_id = uniqid();
            $section_data['items'][$unique_id]['language'] = $language_wise_data;
            $section_data['items'][$unique_id]['id'] = $unique_id;

            $update_data['type']    = $type;
            $update_data['value']   = $section_data;

            try{
                $existingRecord = FrontendHeaderSectionPage::where("type",$type)->where('parent_id',$parent_id)->first();
                if ($existingRecord) {
                    $existingRecord->update($update_data);
                } else {
                    FrontendHeaderSectionPage::create($update_data);
                }
            }catch(Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            return back()->with(['success' => [__("Section item added successfully")]]);

        }
        public function pageItemUpdate(Request $request,$type,$parent_id){
            $request->validate([
                'target'    => "required|string",
            ]);
            $basic_field_name = [
                'name_edit'     => "required|string",
            ];
            $section = FrontendHeaderSectionPage::where("type",$type)->where('parent_id',$parent_id)->first();
            if(!$section) return back()->with(['error' => [__("Section not found!")]]);
            $section_values = json_decode(json_encode($section->value),true);
            if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
            if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);

            $language_wise_data = $this->contentValidate($request,$basic_field_name,"step-edit");
            if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

            $language_wise_data = array_map(function($language) {
                return replace_array_key($language,"_edit");
            },$language_wise_data);

            $section_values['items'][$request->target]['language'] = $language_wise_data;
            try{
                $section->update([
                    'value' => $section_values,
                ]);
            }catch(Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
            return back()->with(['success' => [__("Information updated successfully!")]]);

        }
        public function pageItemDelete(Request $request,$type,$parent_id){
            $request->validate([
                'target'    => 'required|string',
            ]);
            $section = FrontendHeaderSectionPage::where("type",$type)->where('parent_id',$parent_id)->first();
            if(!$section) return back()->with(['error' => [__("Section not found!")]]);
            $section_values = json_decode(json_encode($section->value),true);
            if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
            if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
            try{
                unset($section_values['items'][$request->target]);
                $section->update([
                    'value'     => $section_values,
                ]);
            }catch(Exception $e) {
                return  $e->getMessage();
            }
            return back()->with(['success' => [__("Section item delete successfully!")]]);
        }
    //======================================Header Page Section===============================

    //======================================Header FAQ Section================================
    public function faqIndex($type,$parent_id,$slug){
        $page_title = __("Setup Section");
        $parent = FrontendHeaderSection::where('type',$type)->where('id',$parent_id)->first();
        if(!$parent) return back()->with(['error' => [__("Page record not found in our system.")]]);
        $data = FrontendHeaderSectionFaq::where("type",$type)->where('parent_id',$parent_id)->first();
        $languages = $this->languages;
        return view('admin.sections.header-section.faq.index',compact(
            'page_title',
            'parent',
            'data',
            'type',
            'languages'
        ));
    }
    public function faqUpdate(Request $request,$type,$parent_id){

        $basic_field_name = [
            'heading' => "required|string|max:100",
            'sub_heading' => "required|string|max:200",
        ];
        $parent = FrontendHeaderSection::where('type',$type)->where('id',$parent_id)->first();
        if(!$parent) return back()->with(['error' => [__("Page record not found in our system.")]]);

        $section = FrontendHeaderSectionFaq::where("type",$type)->where('parent_id',$parent_id)->first();
        if($section != null) {
            $data = json_decode(json_encode($section->value),true);
        }else {
            $data = [];
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']           = $data;
        $update_data['type']            = $type;
        $update_data['parent_id']       = $parent_id;
        $update_data['last_edit_by']    = Auth::id();

        try{
            $existingRecord = FrontendHeaderSectionFaq::where("type",$type)->where('parent_id',$parent_id)->first();
            if ($existingRecord) {
                $existingRecord->update($update_data);
            } else {
                FrontendHeaderSectionFaq::create($update_data);
            }
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);

    }
    public function faqItemStore(Request $request,$type,$parent_id){

        $basic_field_name = [
            'question' => "required|string|max:200",
            'answer' => "required|string",
        ];
        $language_wise_data = $this->contentValidate($request,$basic_field_name,"step-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $section = FrontendHeaderSectionFaq::where("type",$type)->where('parent_id',$parent_id)->first();
        if($section != null) {
            $section_data = json_decode(json_encode($section->value),true);
        }else {
            $section_data = [];
        }
        $unique_id = uniqid();
        $section_data['items'][$unique_id]['language'] = $language_wise_data;
        $section_data['items'][$unique_id]['id'] = $unique_id;

        $update_data['type']    = $type;
        $update_data['value']   = $section_data;

        try{
            $existingRecord = FrontendHeaderSectionFaq::where("type",$type)->where('parent_id',$parent_id)->first();
            if ($existingRecord) {
                $existingRecord->update($update_data);
            } else {
                FrontendHeaderSectionFaq::create($update_data);
            }
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section item added successfully")]]);

    }
    public function faqItemUpdate(Request $request,$type,$parent_id){

        $request->validate([
            'target'    => "required|string",
        ]);
        $basic_field_name = [
            'question_edit'     => "required|string|max:100",
            'answer_edit'     => "required|string",
        ];
        $section = FrontendHeaderSectionFaq::where("type",$type)->where('parent_id',$parent_id)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"step-edit");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

        $language_wise_data = array_map(function($language) {
            return replace_array_key($language,"_edit");
        },$language_wise_data);

        $section_values['items'][$request->target]['language'] = $language_wise_data;
        try{
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return back()->with(['success' => [__("Information updated successfully!")]]);

    }
    public function faqItemDelete(Request $request,$type,$parent_id){
        $request->validate([
            'target'    => 'required|string',
        ]);
        $section = FrontendHeaderSectionFaq::where("type",$type)->where('parent_id',$parent_id)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value'     => $section_values,
            ]);
        }catch(Exception $e) {
            return  $e->getMessage();
        }
        return back()->with(['success' => [__("Section item delete successfully!")]]);
    }
    //======================================Header FAQ Section================================



     /**
     * Method for get languages form record with little modification for using only this class
     * @return array $languages
     */
    public function languages() {
        $languages = Language::whereNot('code',LanguageConst::NOT_REMOVABLE)->select("code","name")->get()->toArray();
        $languages[] = [
            'name'      => LanguageConst::NOT_REMOVABLE_CODE,
            'code'      => LanguageConst::NOT_REMOVABLE,
        ];
        return $languages;
    }

    /**
     * Method for validate request data and re-decorate language wise data
     * @param object $request
     * @param array $basic_field_name
     * @return array $language_wise_data
     */
    public function contentValidate($request,$basic_field_name,$modal = null) {
        $languages = $this->languages();

        $current_local = get_default_language_code();
        $validation_rules = [];
        $language_wise_data = [];
        foreach($request->all() as $input_name => $input_value) {
            foreach($languages as $language) {
                $input_name_check = explode("_",$input_name);
                $input_lang_code = array_shift($input_name_check);
                $input_name_check = implode("_",$input_name_check);
                if($input_lang_code == $language['code']) {
                    if(array_key_exists($input_name_check,$basic_field_name)) {
                        $langCode = $language['code'];
                        if($current_local == $langCode) {
                            $validation_rules[$input_name] = $basic_field_name[$input_name_check];
                        }else {
                            $validation_rules[$input_name] = str_replace("required","nullable",$basic_field_name[$input_name_check]);
                        }
                        $language_wise_data[$langCode][$input_name_check] = $input_value;
                    }
                    break;
                }
            }
        }
        if($modal == null) {
            $validated = Validator::make($request->all(),$validation_rules)->validate();
        }else {
            $validator = Validator::make($request->all(),$validation_rules);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with("modal",$modal);
            }
            $validated = $validator->validate();
        }

        return $language_wise_data;
    }

    /**
     * Method for validate request image if have
     * @param object $request
     * @param string $input_name
     * @param string $old_image
     * @return boolean|string $upload
     */
    public function imageValidate($request,$input_name,$old_image) {
        if($request->hasFile($input_name)) {
            $image_validated = Validator::make($request->only($input_name),[
                $input_name         => "image|mimes:png,jpg,webp,jpeg,svg",
            ])->validate();

            $image = get_files_from_fileholder($request,$input_name);
            $upload = upload_files_from_path_dynamic($image,'header-section',$old_image);
            return $upload;
        }

        return false;
    }
}
