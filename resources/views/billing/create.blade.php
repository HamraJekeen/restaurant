@extends('layouts.app')

@section('title', 'Create New Bill')

@section('content')
<div class="container">
    <h2 class="mb-4">Create New Bill</h2>
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('billing.store') }}" method="POST" x-data="billingForm()">
        @csrf
        
        <!-- Billing Information Section -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h4 class="mb-0">Billing Information</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bill_date" class="form-label">Bill Date</label>
                            <input type="date" class="form-control" id="bill_date" name="bill_date" 
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bill_time" class="form-label">Bill Time</label>
                            <input type="time" class="form-control" id="bill_time" name="bill_time" 
                                value="{{ \Carbon\Carbon::now()->format('H:i') }}" required
                                x-init="setInterval(() => $el.value = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }), 1000)">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Items Section -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Billing Items</h4>
                <button type="button" class="btn btn-primary" @click="addItem()">
                    <i class="fas fa-plus me-1"></i>Add Item
                </button>
            </div>
            <div class="card-body">
                <template x-for="(item, index) in items" :key="index">
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Product</label>
                                <select :name="'items['+index+'][product_id]'" class="form-select" 
                                    x-model="item.product_id" @change="updateUnitPrice($event, index)" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                            data-price="{{ number_format($product->price, 2, '.', '') }}">
                                            {{ $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Quantity</label>
                                <div class="input-group">
                                    <input type="number" :name="'items['+index+'][quantity]'" 
                                        class="form-control" x-model="item.quantity" 
                                        min="1" max="20" @input="calculateTotal(index)" required
                                        oninvalid="this.setCustomValidity('Quantity must be between 1 and 20')"
                                        oninput="this.setCustomValidity('')">
                                    <span class="input-group-text" data-bs-toggle="tooltip" 
                                        title="Minimum: 1, Maximum: 20">
                                        <i class="fas fa-info-circle"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Unit Price</label>
                                <input type="number" class="form-control" x-model="item.unit_price" readonly>
                                <input type="hidden" :name="'items['+index+'][unit_price]'" :value="item.unit_price">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea :name="'items['+index+'][description]'" class="form-control" 
                                    x-model="item.description" rows="1" maxlength="255"></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Extra Price</label>
                                <input type="number" :name="'items['+index+'][extra_price]'" 
                                    class="form-control" x-model="item.extra_price" 
                                    min="0" @input="calculateTotal(index)">
                            </div>
                        </div>
                        
                        <div class="col-md-1">
                            <div class="form-group">
                                <label class="form-label">Total</label>
                                <input type="number" class="form-control" x-model="item.total_price" readonly>
                                <input type="hidden" :name="'items['+index+'][total_price]'" :value="item.total_price">
                            </div>
                        </div>
                        
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger w-100" @click="removeItem(index)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <h5 class="text-end mb-0">
                            Total Amount: <span x-text="formatCurrency(totalAmount)" class="text-primary"></span>
                        </h5>
                        <input type="hidden" name="total_amount" :value="totalAmount">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i>Save Bill
            </button>
        </div>
    </form>
</div>

<style>
    .form-group {
        margin-bottom: 0;
    }
    .form-label {
        font-weight: 500;
        margin-bottom: 0.3rem;
    }
    .card-header {
        padding: 0.75rem 1.25rem;
    }
    .input-group-text {
        background-color: #f8f9fa;
    }
    .border-bottom {
        border-bottom: 1px solid #dee2e6 !important;
    }
    .border-bottom:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
</style>
@endsection

@section('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function billingForm() {
        return {
            items: [],
            totalAmount: 0,

            init() {
                this.addItem();
                
                // Update time every second
                setInterval(() => {
                    const now = new Date();
                    document.getElementById('bill_time').value = now.toLocaleTimeString('en-GB', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }, 1000);
            },

            addItem() {
                this.items.push({
                    product_id: '',
                    quantity: 1,
                    unit_price: 0,
                    extra_price: 0,
                    description: '',
                    total_price: 0
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
                this.calculateTotalAmount();
            },

            updateUnitPrice(event, index) {
                const selectedOption = event.target.options[event.target.selectedIndex];
                if (selectedOption) {
                    const price = parseFloat(selectedOption.dataset.price);
                    this.items[index].unit_price = price;
                    this.calculateTotal(index);
                } else {
                    this.items[index].unit_price = 0;
                    this.calculateTotal(index);
                }
            },

            calculateTotal(index) {
                const item = this.items[index];
                const quantity = parseFloat(item.quantity) || 0;
                const unitPrice = parseFloat(item.unit_price) || 0;
                const extraPrice = parseFloat(item.extra_price) || 0;
                
                item.total_price = (quantity * unitPrice) + extraPrice;
                this.calculateTotalAmount();
            },

            calculateTotalAmount() {
                this.totalAmount = this.items.reduce((sum, item) => sum + item.total_price, 0);
            },

            formatCurrency(value) {
                return new Intl.NumberFormat('en-LK', {
                    style: 'currency',
                    currency: 'LKR'
                }).format(value);
            }
        }
    }
</script>
@endsection 