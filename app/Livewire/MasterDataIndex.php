<?php

namespace App\Livewire;

use App\Http\Requests\MasterData\CategoryRequest;
use App\Http\Requests\MasterData\DepartmentRequest;
use App\Http\Requests\MasterData\EquipmentModelRequest;
use App\Http\Requests\MasterData\LocationRequest;
use App\Http\Requests\MasterData\ManufacturerRequest;
use App\Http\Requests\MasterData\SupplierRequest;
use App\Models\Department;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class MasterDataIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $activeTab = 'categories';

    public string $search = '';

    // Modal state
    public bool $showModal = false;

    public ?int $editingId = null;

    // Form fields
    public string $code = '';

    public string $name = '';

    public string $short_name = '';

    public string $description = '';

    public string $country = '';

    public string $website = '';

    public string $specs = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $building = '';

    public string $floor = '';

    public string $room = '';

    public string $head_name = '';

    public ?int $category_id = null;

    public ?int $manufacturer_id = null;

    public ?int $department_id = null;

    public function mount()
    {
        if (! auth()->user()?->can('master_data.manage')) {
            abort(403, 'Bạn không có quyền quản lý danh mục hệ thống.');
        }
    }

    public function updatedActiveTab()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'showModal', 'editingId', 'code', 'name', 'short_name',
            'description', 'country', 'website', 'specs', 'phone',
            'email', 'address', 'building', 'floor', 'room',
            'head_name', 'category_id', 'manufacturer_id', 'department_id',
        ]);
        $this->resetValidation();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id)
    {
        $this->resetForm();
        $this->editingId = $id;

        switch ($this->activeTab) {
            case 'categories':
                $cat = EquipmentCategory::findOrFail($id);
                $this->code = $cat->code;
                $this->name = $cat->name;
                $this->description = $cat->description ?? '';
                break;
            case 'manufacturers':
                $mfg = Manufacturer::findOrFail($id);
                $this->code = $mfg->code;
                $this->name = $mfg->name;
                $this->country = $mfg->country ?? '';
                $this->website = $mfg->website ?? '';
                break;
            case 'models':
                $mod = EquipmentModel::findOrFail($id);
                $this->code = $mod->code;
                $this->name = $mod->name;
                $this->category_id = $mod->category_id;
                $this->manufacturer_id = $mod->manufacturer_id;
                $this->specs = $mod->specs ?? '';
                break;
            case 'suppliers':
                $sup = Supplier::findOrFail($id);
                $this->code = $sup->code;
                $this->name = $sup->name;
                $this->phone = $sup->phone ?? '';
                $this->email = $sup->email ?? '';
                $this->address = $sup->address ?? '';
                break;
            case 'locations':
                $loc = Location::findOrFail($id);
                $this->code = $loc->code;
                $this->name = $loc->name;
                $this->department_id = $loc->department_id;
                $this->building = $loc->building ?? '';
                $this->floor = $loc->floor ?? '';
                $this->room = $loc->room ?? '';
                break;
            case 'departments':
                $dept = Department::findOrFail($id);
                if ($dept->code === 'CHUA-PHAN-KHOA') {
                    session()->flash('error', 'Không thể sửa khoa Chưa phân khoa hệ thống.');

                    return;
                }
                $this->code = $dept->code;
                $this->name = $dept->name;
                $this->short_name = $dept->short_name ?? '';
                $this->phone = $dept->phone ?? '';
                $this->head_name = $dept->head_name ?? '';
                break;
        }

        $this->showModal = true;
    }

    public function save()
    {
        if (! auth()->user()?->can('master_data.manage')) {
            abort(403);
        }

        switch ($this->activeTab) {
            case 'categories':
                $req = new CategoryRequest;
                $rules = $req->rules();
                if ($this->editingId) {
                    $rules['code'][3] = Rule::unique('equipment_categories', 'code')->ignore($this->editingId);
                }
                $validated = $this->validate($rules, $req->messages());

                EquipmentCategory::updateOrCreate(['id' => $this->editingId], $validated);
                session()->flash('message', $this->editingId ? 'Cập nhật loại thiết bị thành công.' : 'Thêm mới loại thiết bị thành công.');
                break;

            case 'manufacturers':
                $req = new ManufacturerRequest;
                $rules = $req->rules();
                if ($this->editingId) {
                    $rules['code'][3] = Rule::unique('manufacturers', 'code')->ignore($this->editingId);
                }
                $validated = $this->validate($rules, $req->messages());

                Manufacturer::updateOrCreate(['id' => $this->editingId], $validated);
                session()->flash('message', $this->editingId ? 'Cập nhật hãng sản xuất thành công.' : 'Thêm mới hãng sản xuất thành công.');
                break;

            case 'models':
                $req = new EquipmentModelRequest;
                $rules = $req->rules();
                if ($this->editingId) {
                    $rules['code'][3] = Rule::unique('equipment_models', 'code')->ignore($this->editingId);
                }
                $validated = $this->validate($rules, $req->messages());

                EquipmentModel::updateOrCreate(['id' => $this->editingId], $validated);
                session()->flash('message', $this->editingId ? 'Cập nhật Model thành công.' : 'Thêm mới Model thành công.');
                break;

            case 'suppliers':
                $req = new SupplierRequest;
                $rules = $req->rules();
                if ($this->editingId) {
                    $rules['code'][3] = Rule::unique('suppliers', 'code')->ignore($this->editingId);
                }
                $validated = $this->validate($rules, $req->messages());

                Supplier::updateOrCreate(['id' => $this->editingId], $validated);
                session()->flash('message', $this->editingId ? 'Cập nhật nhà cung cấp thành công.' : 'Thêm mới nhà cung cấp thành công.');
                break;

            case 'locations':
                $req = new LocationRequest;
                $rules = $req->rules();
                if ($this->editingId) {
                    $rules['code'][3] = Rule::unique('locations', 'code')->ignore($this->editingId);
                }
                $validated = $this->validate($rules, $req->messages());

                Location::updateOrCreate(['id' => $this->editingId], $validated);
                session()->flash('message', $this->editingId ? 'Cập nhật vị trí thành công.' : 'Thêm mới vị trí thành công.');
                break;

            case 'departments':
                if ($this->editingId) {
                    $existingDept = Department::find($this->editingId);
                    if ($existingDept && $existingDept->code === 'CHUA-PHAN-KHOA') {
                        session()->flash('error', 'Không thể chỉnh sửa khoa Chưa phân khoa hệ thống.');
                        $this->resetForm();

                        return;
                    }
                }
                $req = new DepartmentRequest;
                $rules = $req->rules();
                if ($this->editingId) {
                    $rules['code'][3] = Rule::unique('departments', 'code')->ignore($this->editingId);
                }
                $validated = $this->validate($rules, $req->messages());

                Department::updateOrCreate(['id' => $this->editingId], $validated);
                session()->flash('message', $this->editingId ? 'Cập nhật khoa/phòng thành công.' : 'Thêm mới khoa/phòng thành công.');
                break;
        }

        $this->resetForm();
    }

    public function delete(int $id)
    {
        if (! auth()->user()?->can('master_data.manage')) {
            abort(403);
        }

        switch ($this->activeTab) {
            case 'categories':
                $cat = EquipmentCategory::findOrFail($id);
                if ($cat->equipment()->count() > 0 || $cat->equipmentModels()->count() > 0) {
                    session()->flash('error', 'Không thể xóa loại thiết bị này vì còn thiết bị hoặc model tham chiếu!');

                    return;
                }
                $cat->delete();
                session()->flash('message', 'Đã xóa mềm loại thiết bị thành công.');
                break;

            case 'manufacturers':
                $mfg = Manufacturer::findOrFail($id);
                if ($mfg->equipment()->count() > 0 || $mfg->equipmentModels()->count() > 0) {
                    session()->flash('error', 'Không thể xóa hãng sản xuất này vì còn thiết bị hoặc model tham chiếu!');

                    return;
                }
                $mfg->delete();
                session()->flash('message', 'Đã xóa mềm hãng sản xuất thành công.');
                break;

            case 'models':
                $mod = EquipmentModel::findOrFail($id);
                if ($mod->equipment()->count() > 0) {
                    session()->flash('error', 'Không thể xóa Model này vì còn thiết bị tham chiếu!');

                    return;
                }
                $mod->delete();
                session()->flash('message', 'Đã xóa mềm Model thành công.');
                break;

            case 'suppliers':
                $sup = Supplier::findOrFail($id);
                if ($sup->equipment()->count() > 0) {
                    session()->flash('error', 'Không thể xóa nhà cung cấp này vì còn thiết bị tham chiếu!');

                    return;
                }
                $sup->delete();
                session()->flash('message', 'Đã xóa mềm nhà cung cấp thành công.');
                break;

            case 'locations':
                $loc = Location::findOrFail($id);
                if ($loc->equipment()->count() > 0) {
                    session()->flash('error', 'Không thể xóa vị trí này vì còn thiết bị tham chiếu!');

                    return;
                }
                $loc->delete();
                session()->flash('message', 'Đã xóa mềm vị trí thành công.');
                break;

            case 'departments':
                $dept = Department::findOrFail($id);
                if ($dept->code === 'CHUA-PHAN-KHOA') {
                    session()->flash('error', 'Không thể xóa khoa Chưa phân khoa hệ thống!');

                    return;
                }
                if ($dept->equipment()->count() > 0 || $dept->users()->count() > 0 || $dept->locations()->count() > 0) {
                    session()->flash('error', 'Không thể xóa khoa/phòng này vì còn thiết bị, vị trí hoặc người dùng thuộc khoa!');

                    return;
                }
                $dept->delete();
                session()->flash('message', 'Đã xóa mềm khoa/phòng thành công.');
                break;
        }
    }

    public function render()
    {
        return view('livewire.master-data-index', [
            'categories' => EquipmentCategory::withCount(['equipment', 'equipmentModels'])->get(),
            'manufacturers' => Manufacturer::withCount(['equipment', 'equipmentModels'])->get(),
            'models' => EquipmentModel::with(['category', 'manufacturer'])->withCount('equipment')->get(),
            'suppliers' => Supplier::withCount('equipment')->get(),
            'locations' => Location::with('department')->withCount('equipment')->get(),
            'departments' => Department::withCount(['equipment', 'users', 'locations'])->get(),
        ])->layout('components.layouts.app', ['title' => 'Quản lý Danh mục Hệ thống - MEMS']);
    }
}
