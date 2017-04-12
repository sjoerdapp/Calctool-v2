<?php

namespace CalculatieTool\Http\Controllers\Relation;

use Illuminate\Http\Request;
use JeroenDesloovere\VCard\VCard;

use \CalculatieTool\Models\Relation;
use \CalculatieTool\Models\RelationKind;
use \CalculatieTool\Models\Contact;
use \CalculatieTool\Models\Audit;
use \CalculatieTool\Models\ContactFunction;
use \CalculatieTool\Models\Resource;
use CalculatieTool\Http\Controllers\Controller;

use \Auth;
use \Image;
use \Storage;

class RelationController extends Controller {

    /**
     * Display a listing of the resource.
     * GET /relation
     *
     * @return Response
     */

    public function getNew()
    {
        return view('user.new_relation', ['debtor_code' => mt_rand(1000000, 9999999)]);
    }

    public function getEdit()
    {
        return view('user.edit_relation');
    }

    public function getNewContact()
    {
        return view('user.new_contact');
    }

    public function getEditContact()
    {
        return view('user.edit_contact');
    }

    public function getMyCompany()
    {
        return view('user.edit_mycompany');
    }

    public function doUpdateMyCompany(Request $request)
    {
        $this->validate($request, [
            /* General */
            'id' => array('required','integer'),
            /* Company */
            'company_type' => array('required_if:relationkind,zakelijk','numeric'),
            'company_name' => array('required_if:relationkind,zakelijk','max:50'),
            'kvk' => array('numeric','min:8'),
            'btw' => array('alpha_num','min:14'),
            'telephone_comp' => array('alpha_num','max:12'),
            'email_comp' => array('required_if:relationkind,zakelijk','email','max:80'),
            /* Adress */
            'street' => array('required','max:60'),
            'address_number' => array('required','alpha_num','max:5'),
            'zipcode' => array('required','size:6'),
            'city' => array('required','max:35'),
            'province' => array('required','numeric'),
            'country' => array('required','numeric')
        ]);

        /* General */
        $relation = Relation::find($request->input('id'));
        if (!$relation || !$relation->isOwner()) {
            return Redirect::back()->withInput($request->all());
        }
        $relation->note = $request->input('note');

        /* Company */
        $relation_kind = RelationKind::where('id',$relation->kind_id)->firstOrFail();
        if ($relation_kind->kind_name == "zakelijk") {
            $relation->company_name = $request->input('company_name');
            $relation->type_id = $request->input('company_type');
            if (!$request->has('kvk'))
                $relation->kvk = NULL;
            else
                $relation->kvk = $request->input('kvk');
            if (!$request->input('btw'))
                $relation->btw = NULL;
            else
                $relation->btw = $request->input('btw');
            $relation->phone = $request->input('telephone_comp');
            $relation->email = $request->input('email_comp');
            $relation->website = $request->input('website');
        }

        /* Adress */
        $relation->address_street = $request->input('street');
        $relation->address_number = $request->input('address_number');
        $relation->address_postal = $request->input('zipcode');
        $relation->address_city = $request->input('city');
        $relation->province_id = $request->input('province');
        $relation->country_id = $request->input('country');

        $relation->save();

        Audit::CreateEvent('mycompany.update.success', 'Settings for my corporation updated');
        
        return redirect('/mycompany/?multipage=true')->with('success', 'Uw bedrijfsgegevens zijn aangepast');
    }

    public function doUpdate(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer'),
            'debtor' => array('required','alpha_num','max:10'),
            'company_type' => array('required_if:relationkind,zakelijk','numeric'),
            'company_name' => array('required_if:relationkind,zakelijk','max:50'),
            'email_comp' => array('required_if:relationkind,zakelijk','email','max:80'),
            'street' => array('required','max:60'),
            'address_number' => array('required','alpha_num','max:5'),
            'zipcode' => array('required','size:6'),
            'city' => array('required','max:35'),
            'province' => array('required','numeric'),
            'country' => array('required','numeric')
        ]);

        /* General */
        $relation = \CalculatieTool\Models\Relation::find($request->input('id'));
        if (!$relation || !$relation->isOwner()) {
            return back()->withInput($request->all());
        }
        $relation->note = $request->input('note');
        $relation->debtor_code = $request->input('debtor');

        /* Company */
        $relation_kind = \CalculatieTool\Models\RelationKind::find($relation->kind_id);
        if ($relation_kind->kind_name == "zakelijk") {
            $relation->company_name = $request->input('company_name');
            $relation->type_id = $request->input('company_type');
            $relation->kvk = $request->input('kvk');
            $relation->btw = $request->input('btw');
            $relation->phone = $request->input('telephone_comp');
            $relation->email = $request->input('email_comp');
            $relation->website = $request->input('website');
        }

        /* Adress */
        $relation->address_street = $request->input('street');
        $relation->address_number = $request->input('address_number');
        $relation->address_postal = $request->input('zipcode');
        $relation->address_city = $request->input('city');
        $relation->province_id = $request->input('province');
        $relation->country_id = $request->input('country');

        $relation->save();

        return back()->with('success', 'Relatie is aangepast');
    }

    public function getDelete(Request $request, $relation_id)
    {
        $relation = \CalculatieTool\Models\Relation::find($relation_id);
        if (!$relation || !$relation->isOwner()) {
            return back()->withInput($request->all());
        }

        $relation->active = false;

        $relation->save();

        return redirect('/relation');
    }

    public function getConvert(Request $request, $relation_id)
    {
        $relation = \CalculatieTool\Models\Relation::find($relation_id);
        if (!$relation || !$relation->isOwner()) {
            return back()->withInput($request->all());
        }

        if (\CalculatieTool\Models\RelationKind::find($relation->kind_id)->kind_name == 'zakelijk') {
            $relation->kind_id = \CalculatieTool\Models\RelationKind::where('kind_name','particulier')->first()->id;
        } else {
            $relation->kind_id = \CalculatieTool\Models\RelationKind::where('kind_name','zakelijk')->first()->id;
            if (!$relation->company_name)
                $relation->company_name = 'onbekend';
            if (!$relation->email)
                $relation->email = 'onbekend@calculatietool.com';
        }

        // $relation->active = false;

        $relation->save();

        return back()->with('success', 'Relatie is omgezet');
    }

    public function doUpdateContact(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer'),
            'contact_salutation' => array('max:16'),
            'contact_name' => array('required','max:50'),
            'contact_firstname' => array('max:30'),
            'email' => array('required','email','max:80'),
        ]);

        $contact = \CalculatieTool\Models\Contact::find($request->input('id'));
        if (!$contact) {
            return back()->withInput($request->all());
        }
        $relation = \CalculatieTool\Models\Relation::find($contact->relation_id);
        if (!$relation || !$relation->isOwner()) {
            return back()->withInput($request->all());
        }

        if ($request->input('contact_salutation')) {
            $contact->salutation = $request->input('contact_salutation');
        }
        if ($request->input('contact_firstname')) {
            $contact->firstname = $request->input('contact_firstname');
        }
        $contact->lastname = $request->input('contact_name');
        $contact->mobile = $request->input('mobile');
        $contact->phone = $request->input('telephone');
        $contact->email = $request->input('email');
        $contact->note = $request->input('note');
        if ($request->input('contactfunction')) {
            $contact->function_id = $request->input('contactfunction');
        }
        if ($request->input('gender') == '-1') {
            $contact->gender = NULL;
        } else {
            $contact->gender = $request->input('gender');
        }

        $contact->save();

        return back()->with('success', 'Contactgegevens zijn aangepast');
    }

    public function doUpdateIban(Request $request)
    {
        $this->validate($request, [
            'iban' => array('alpha_num','max:25'),
            'iban_name' => array('max:50'),
        ]);

        $relation = \CalculatieTool\Models\Relation::find($request->input('id'));
        if (!$relation || !$relation->isOwner()) {
            return back()->withInput($request->all());
        }

        $relation->iban = $request->input('iban');
        $relation->iban_name = $request->input('iban_name');

        $relation->save();

        return back()->with('success', 'Betalingsgegevens zijn aangepast');
    }

    public function doNewMyCompany(Request $request)
    {
        $this->validate($request, [
            /* Company */
            'company_type' => array('required_if:relationkind,zakelijk','numeric'),
            'company_name' => array('required_if:relationkind,zakelijk','max:50'),
            'kvk' => array('numeric','min:8'),
            'btw' => array('alpha_num','min:14'),
            'telephone_comp' => array('alpha_num','max:12'),
            'email_comp' => array('required_if:relationkind,zakelijk','email','max:80'),
            /* Adress */
            'street' => array('required','max:60'),
            'address_number' => array('required','alpha_num','max:5'),
            'zipcode' => array('required','size:6'),
            'city' => array('required','max:35'),
            'province' => array('required','numeric'),
            'country' => array('required','numeric'),
            'website' => array('max:180'),
        ]);

        /* General */
        $relation = new Relation;
        $relation->user_id = Auth::id();
        $relation->note = $request->input('note');
        $relation->debtor_code = mt_rand(1000000, 9999999);

        /* Company */
        $relation->kind_id = RelationKind::where('kind_name','=','zakelijk')->first()->id;
        $relation->company_name = $request->input('company_name');
        $relation->type_id = $request->input('company_type');
        $relation->kvk = $request->input('kvk');
        $relation->btw = $request->input('btw');
        $relation->phone = $request->input('telephone_comp');
        $relation->email = $request->input('email_comp');
        $relation->website = $request->input('website');

        /* Adress */
        $relation->address_street = $request->input('street');
        $relation->address_number = $request->input('address_number');
        $relation->address_postal = $request->input('zipcode');
        $relation->address_city = $request->input('city');
        $relation->province_id = $request->input('province');
        $relation->country_id = $request->input('country');

        $relation->save();

        $user = Auth::user();
        $user->self_id = $relation->id;
        $user->save();

        Audit::CreateEvent('mycompany.new.success', 'Settings for my corporation created');

        return back()->with('success', 'Uw bedrijfsgegevens zijn opgeslagen');
    }

    public function doNew(Request $request)
    {
        $rules = array(
            'relationkind' => array('required','numeric'),
            'debtor' => array('required','alpha_num','max:10'),
            'company_type' => array('required_if:relationkind,1','numeric'),
            'company_name' => array('required_if:relationkind,1','max:50'),
            'email_comp' => array('required_if:relationkind,1','email','max:80'),
            'contact_salutation' => array('max:16'),
            'contact_name' => array('required','max:50'),
            'contact_firstname' => array('max:30'),
            'email' => array('required','email','max:80'),
            'contactfunction' => array('required','numeric'),
            'street' => array('required','max:60'),
            'address_number' => array('required','alpha_num','max:5'),
            'zipcode' => array('required','size:6'),
            'city' => array('required','max:35'),
            'province' => array('required','numeric'),
            'country' => array('required','numeric'),
            'telephone' => array('max:12'),
            'mobile' => array('max:12'),
            'website' => array('max:180'),
            'iban' => array('alpha_num','max:25'),
            'iban_name' => array('max:50'),
        );

        $this->validate($request, $rules);

        /* General */
        $relation = new \CalculatieTool\Models\Relation;
        $relation->user_id = \Auth::id();
        $relation->note = $request->input('note');
        $relation->kind_id = $request->input('relationkind');
        $relation->debtor_code = $request->input('debtor');

        /* Company */
        $relation_kind = \CalculatieTool\Models\RelationKind::where('id','=',$relation->kind_id)->firstOrFail();
        if ($relation_kind->kind_name == "zakelijk") {
            $relation->company_name = $request->input('company_name');
            $relation->type_id = $request->input('company_type');
            $relation->kvk = $request->input('kvk');
            $relation->btw = $request->input('btw');
            $relation->phone = $request->input('telephone_comp');
            $relation->email = $request->input('email_comp');
            $relation->website = $request->input('website');
        }

        /* Adress */
        $relation->address_street = $request->input('street');
        $relation->address_number = $request->input('address_number');
        $relation->address_postal = $request->input('zipcode');
        $relation->address_city = $request->input('city');
        $relation->province_id = $request->input('province');
        $relation->country_id = $request->input('country');

        if ($request->input('iban'))
            $relation->iban = $request->input('iban');
        if ($request->input('iban_name'))
            $relation->iban_name = $request->input('iban_name');

        $relation->save();

        /* Contact */
        $contact = new \CalculatieTool\Models\Contact;
        $contact->salutation = $request->input('contact_salutation');
        $contact->firstname = $request->input('contact_firstname');
        $contact->lastname = $request->input('contact_name');
        $contact->mobile = $request->input('mobile');
        $contact->phone = $request->input('telephone');
        $contact->email = $request->input('email');
        $contact->note = $request->input('note');
        $contact->relation_id = $relation->id;
        if ($relation_kind->kind_name == "zakelijk") {
            $contact->function_id = $request->input('contactfunction');
        } else {
            $contact->function_id = ContactFunction::where('function_name','=','opdrachtgever')->first()->id;
        }
        if ($request->input('gender') == '-1') {
            $contact->gender = NULL;
        } else {
            $contact->gender = $request->input('gender');
        }

        $contact->save();

        if ($request->get('redirect'))
            return redirect('/'.$request->get('redirect'));

        if ($request->ajax()) {
            if ($relation_kind->kind_name == "zakelijk")
                return response()->json(['success' => true, 'id' => $relation->id, 'name' => $relation->company_name]);
            else
                return response()->json(['success' => true, 'id' => $relation->id, 'name' => $contact->firstname . ' ' . $contact->lastname]);
        }

        return redirect('/relation-'.$relation->id.'/edit')->with('success', 'Relatie opgeslagen');
    }

    public function doNewContact(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer'),
            'contact_salutation' => array('max:16'),
            'contact_firstname' => array('required','max:50'),
            'contact_name' => array('required','max:50'),
            'email' => array('required','email','max:80'),
        ]);

        $relation = \CalculatieTool\Models\Relation::find($request->input('id'));
        if (!$relation || !$relation->isOwner()) {
            return back()->withInput($request->all());
        }

        $contact = new \CalculatieTool\Models\Contact;
        $contact->salutation = $request->input('contact_salutation');
        $contact->firstname = $request->input('contact_firstname');
        $contact->lastname = $request->input('contact_name');
        $contact->mobile = $request->input('mobile');
        $contact->phone = $request->input('telephone');
        $contact->email = $request->input('email');
        $contact->note = $request->input('note');
        $contact->relation_id = $relation->id;
        if (\CalculatieTool\Models\RelationKind::find($relation->kind_id)->kind_name=='zakelijk') {
            $contact->function_id = $request->input('contactfunction');
        } else {
            $contact->function_id = ContactFunction::where('function_name','=','opdrachtgever')->first()->id;
        }
        if ($request->input('gender') == '-1') {
            $contact->gender = NULL;
        } else {
            $contact->gender = $request->input('gender');
        }

        $contact->save();

        return redirect('/relation-'.$request->input('id').'/edit')->with('success','Contact opgeslagen');
    }

    public function doMyCompanyNewContact(Request $request)
    {
        $this->validate($request, [
            /* Contact */
            'id' => array('required','integer'),
            'contact_salutation' => array('max:16'),
            'contact_name' => array('required','max:50'),
            'email' => array('required','email','max:80'),
            'contactfunction' => array('required','numeric'),
        ]);

        $relation = Relation::find($request->input('id'));
        if (!$relation || !$relation->isOwner()) {
            return Redirect::back()->withInput($request->all());
        }

        $contact = new Contact;
        $contact->salutation = $request->input('contact_salutation');
        $contact->firstname = $request->input('contact_firstname');
        $contact->lastname = $request->input('contact_name');
        $contact->mobile = $request->input('mobile');
        $contact->phone = $request->input('telephone');
        $contact->email = $request->input('email');
        $contact->note = $request->input('note');
        $contact->relation_id = $relation->id;
        $contact->function_id = $request->input('contactfunction');
        if ($request->input('gender') == '-1') {
            $contact->gender = NULL;
        } else {
            $contact->gender = $request->input('gender');
        }

        $contact->save();

        return redirect('/mycompany')->with('success', 'Nieuw contact aangemaakt');
    }

    public function doDeleteContact()
    {
        $rules = array(
            'id' => array('required','integer'),
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->messages();

            // redirect our user back to the form with the errors from the validator
            return Redirect::back()->withErrors($validator)->withInput($request->all());
        } else {

            $rec = Contact::find($request->input('id'));
            if (!$rec)
                return Redirect::back()->withInput($request->all());
            $relation = Relation::find($rec->relation_id);
            if (!$relation || !$relation->isOwner()) {
                return Redirect::back()->withInput($request->all());
            }

            $rec->delete();

            return Redirect::back()->with('success', 'Contact verwijderd');
        }
    }

    public function doUpdateProfit(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer'),
            'hour_rate' => array('regex:/^\$?([0-9]{1,3},([0-9]{3},)*[0-9]{3}|[0-9]+)(.[0-9][0-9]?)?$/'),
            'more_hour_rate' => array('required','regex:/^\$?([0-9]{1,3},([0-9]{3},)*[0-9]{3}|[0-9]+)(.[0-9][0-9]?)?$/'),
            'profit_material_1' => array('numeric','between:0,200'),
            'profit_equipment_1' => array('numeric','between:0,200'),
            'profit_material_2' => array('numeric','between:0,200'),
            'profit_equipment_2' => array('numeric','between:0,200'),
            'more_profit_material_1' => array('required','numeric','between:0,200'),
            'more_profit_equipment_1' => array('required','numeric','between:0,200'),
            'more_profit_material_2' => array('required','numeric','between:0,200'),
            'more_profit_equipment_2' => array('required','numeric','between:0,200')
        ]);

        $relation = Relation::find($request->input('id'));
        if (!$relation || !$relation->isOwner()) {
            return back()->withInput($request->all());
        }

        $hour_rate = floatval(str_replace(',', '.', str_replace('.', '', $request->input('hour_rate'))));
        if ($hour_rate<0 || $hour_rate>999) {
            return back()->withInput($request->all())->withErrors(['error' => "Ongeldige invoer, vervang punten door comma's"]);
        }

        $hour_rate_more = floatval(str_replace(',', '.', str_replace('.', '', $request->input('more_hour_rate'))));
        if ($hour_rate_more<0 || $hour_rate_more>999) {
            return back()->withInput($request->all())->withErrors(['error' => "Ongeldige invoer, vervang punten door comma's"]);
        }

        if ($hour_rate)
            $relation->hour_rate = $hour_rate;
            $relation->hour_rate_more = $hour_rate_more;
        if ($request->input('profit_material_1') != "")
            $relation->profit_calc_contr_mat = round($request->input('profit_material_1'));
        if ($request->input('profit_equipment_1') != "")
            $relation->profit_calc_contr_equip = round($request->input('profit_equipment_1'));
        if ($request->input('profit_material_2') != "")
            $relation->profit_calc_subcontr_mat = round($request->input('profit_material_2'));
        if ($request->input('profit_equipment_2') != "")
            $relation->profit_calc_subcontr_equip = round($request->input('profit_equipment_2'));
        $relation->profit_more_contr_mat = round($request->input('more_profit_material_1'));
        $relation->profit_more_contr_equip = round($request->input('more_profit_equipment_1'));
        $relation->profit_more_subcontr_mat = round($request->input('more_profit_material_2'));
        $relation->profit_more_subcontr_equip = round($request->input('more_profit_equipment_2'));

        $relation->save();

        Audit::CreateEvent('relation.update.profit.success', 'Profits by relation ' . $relation->id . ' updated');

        return back()->with('success', 'Uurtarief & winstpercentages aangepast');
    }

    public function getAll()
    {
        return view('user.relation');
    }

    public function doNewLogo(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer'),
            'image' => array('required', 'mimes:jpeg,bmp,png,gif'),
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $path = Storage::putFile(Auth::user()->encodedName(), $file);
            if (!$path) {
                return back()->withErrors(['msg' => 'Upload mislukt']);
            }

            $path = config('filesystems.disks.local.root') . '/' . $path;
            $image = Image::make($path)->resize(null, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save();

            $resource = new Resource;
            $resource->resource_name = 'Bedrijfslogo';
            $resource->file_location = $path;
            $resource->file_size = $image->filesize();
            $resource->user_id = Auth::id();
            $resource->description = 'Relatielogo';
            $resource->save();

            $relation = Relation::find($request->input('id'));
            if (!$relation || !$relation->isOwner()) {
                return back()->withInput($request->all());
            }
            $relation->logo_id = $resource->id;

            $relation->save();

            return back()->with('success', 'Uw logo is geupload');
        } else {

            $messages->add('file', 'Geen afbeelding geupload');

            // redirect our user back to the form with the errors from the validator
            return back()->withErrors($messages);
        }

    }

    public function doNewAgreement(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer'),
            'doc' => array('required', 'mimes:pdf'),
        ]);

        if ($request->hasFile('doc')) {
            $file = $request->file('doc');
            if (strlen($file->getClientOriginalName()) >= 50) {
                return back()->withErrors(['msg' => 'Bestandsnaam te lang']);
            }

            $path = Storage::putFile(Auth::user()->encodedName(), $file);
            if (!$path) {
                return back()->withErrors(['msg' => 'Upload mislukt']);
            }

            $resource = new Resource;
            $resource->resource_name = $file->getClientOriginalName();
            $resource->file_location = config('filesystems.disks.local.root') . '/' . $path;
            $resource->file_size = $file->getClientSize();
            $resource->user_id = Auth::id();
            $resource->description = 'Algemene Voorwaarden';
            $resource->save();

            $relation = Relation::find($request->input('id'));
            if (!$relation || !$relation->isOwner()) {
                return back()->withInput($request->all());
            }
            $relation->agreement_id = $resource->id;

            $relation->save();

            return back()->with('success', 'Uw algemene voorwaarden zijn geupload');
        } else {

            // redirect our user back to the form with the errors from the validator
            return back()->withErrors(['file' => 'Geen document geupload']);
        }

    }

    public function downloadVCard(Request $request, $relation_id, $contact_id)
    {
        $contact = \CalculatieTool\Models\Contact::find($contact_id);
        if (!$contact) {
            return;
        } else {
            $relation = \CalculatieTool\Models\Relation::find($contact->relation_id);
            if (!$relation || !$relation->isOwner()) {
                return;
            }
        }

        // define vcard
        $vcard = new VCard();

        // define variables
        $additional = '';
        $prefix = '';
        $suffix = '';

        // add personal data
        $vcard->addName($contact->lastname, $contact->firstname, $additional, $prefix, $suffix);

        // add work data
        $vcard->addCompany($relation->company_name);
        $vcard->addJobtitle(ucwords(\CalculatieTool\Models\ContactFunction::find($contact->function_id)->function_name));
        $vcard->addEmail($relation->email);
        if ($relation->phone)
            $vcard->addPhoneNumber($relation->phone, 'WORK');
        if ($relation->mobile)
            $vcard->addPhoneNumber($relation->mobile, 'WORK');

        return $vcard->download();
    }
}