<?php
include __DIR__ . '/parts/init.php';

$title = '新增商品';
$pageName = 'products_add';
$brands = $pdo->query("SELECT brand_id, name FROM brands ORDER BY name ASC")->fetchAll();
$sports = $pdo->query("SELECT id, name FROM sports ORDER BY name ASC")->fetchAll();
?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<form id="productForm" enctype="multipart/form-data" novalidate>
  <div class="container">
    <div class="row mb-1">
      <div class="col-1 d-none d-md-block"></div>
      <div class="col-12 col-md-7 mb-3">
        <div class="card">
          <div class="card-body">
            <div class="mb-3">
              <label for="product_name" class="form-label">商品名稱 <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="product_name" name="product_name" required>
              <div class="form-text text-danger product_name_error"></div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6 mb-3 mb-md-0">
                <label for="sport" class="form-label">運動種類 <span class="text-danger">*</span></label>
                <div class="input-group">
                  <select class="form-select" id="sport" name="sport" required>
                    <option value="">請選擇</option>
                    <?php foreach ($sports as $s): ?>
                      <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addSportModal" title="新增運動種類">
                    <i class="fa-solid fa-plus"></i>
                  </button>
                </div>
                <div class="form-text text-danger sport_error"></div>
              </div>
              <div class="col-md-6">
                <label for="brand" class="form-label">品牌 <span class="text-danger">*</span></label>
                <div class="input-group">
                  <select class="form-select" id="brand" name="brand" required>
                    <option value="">請選擇</option>
                    <?php foreach ($brands as $b): ?>
                      <option value="<?= $b['brand_id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addBrandModal" title="新增品牌">
                    <i class="fa-solid fa-plus"></i>
                  </button>
                </div>
                <div class="form-text text-danger brand_error"></div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6 mb-3 mb-md-0">
                <label for="stock" class="form-label">數量 <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="stock" name="stock" required min="0">
                <div class="form-text text-danger stock_error"></div>
              </div>
              <div class="col-md-6">
                <label for="price" class="form-label">單價 <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="price" name="price" required min="1">
                <div class="form-text text-danger price_error"></div>
              </div>
            </div>

            <div class="mb-3">
              <label for="material" class="form-label">材質 <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="material" name="material" required>
              <div class="form-text text-danger material_error"></div>
            </div>

            <div class="mb-3">
              <label for="size" class="form-label">尺寸 (公分) <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="size" name="size" required>
              <div class="form-text text-danger size_error"></div>
            </div>

            <div class="mb-3">
              <label for="weight" class="form-label">重量 (公克) <span class="text-danger">*</span></label>
              <input type="number" step="0.1" class="form-control" id="weight" name="weight" required min="0.1">
              <div class="form-text text-danger weight_error"></div>
            </div>

            <div class="mb-3">
              <label for="color" class="form-label">款式<span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="color" name="color" required>
              <div class="form-text text-danger color_error"></div>
            </div>

            <div class="mb-3">
              <label for="origin" class="form-label">產地 <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="origin" name="origin" required>
              <div class="form-text text-danger origin_error"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="card">
          <div class="card-header">
            商品圖片 <span class="text-danger">*</span>
          </div>
          <div class="card-body">
            <input type="file" id="productImages" name="productImages[]" multiple accept="image/*" style="display: none;">
            <label for="productImages" class="btn btn-success btn-sm text-light ">
              <i class="fa-solid fa-image me-1"></i> 選擇圖片
            </label>
            <div id="previewContainer" class="d-flex flex-wrap gap-2 mt-3 p-2 rounded" style="min-height: 80px;">
            </div>
            <div id="imageError" class="form-text text-danger mt-1"></div>
          </div>
        </div>
      </div>
      <div class="col-1 d-none d-md-block"></div>
    </div>
    <div class="row mb-5">
      <div class="col-1 d-none d-md-block"></div>
      <div class="col-12 col-md-10">
        <button type="submit" class="btn btn-primary">新增</button>
        <a href="products_list.php" class="btn btn-outline-secondary ms-2">取消</a>
      </div>
    </div>
  </div>
</form>

<div class="modal fade" id="addSportModal" tabindex="-1" aria-labelledby="addSportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addSportModalLabel">新增運動種類</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="newSportName" class="form-label">運動種類名稱 <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="newSportName" placeholder="輸入運動種類名稱">
        </div>
        <div class="form-text text-danger mt-1" id="newSportError"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary" id="saveNewSport">儲存</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addBrandModalLabel">新增品牌</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="newBrandName" class="form-label">品牌名稱 <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="newBrandName" placeholder="輸入品牌名稱">
        </div>
        <div class="form-text text-danger mt-1" id="newBrandError"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary" id="saveNewBrand">儲存</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="successModalLabel">新增成功</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        商品已成功新增！
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">繼續新增</button>
        <a href="products_list.php" class="btn btn-primary">回列表頁</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script>
  const form = document.getElementById("productForm");
  const imageInput = document.getElementById("productImages");
  const previewContainer = document.getElementById("previewContainer");
  const imageError = document.getElementById("imageError");

  const sportSelect = document.getElementById('sport');
  const brandSelect = document.getElementById('brand');
  const sportErrorEl = document.querySelector('.sport_error');
  const brandErrorEl = document.querySelector('.brand_error');

  let selectedFiles = []; // 這個陣列將累計所有選擇的檔案


  function renderPreviews() {
    previewContainer.innerHTML = "";
    selectedFiles.forEach((file, index) => {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          const wrapper = document.createElement("div");
          wrapper.classList.add("position-relative", "p-1");
          wrapper.style.width = "88px";
          wrapper.style.height = "88px";
          wrapper.innerHTML = `
            <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #ddd; border-radius: .25rem;">
            <button type="button" class="btn-close btn-sm position-absolute top-0 end-0 bg-light p-1" aria-label="移除此圖片" data-index="${index}" style="transform: translate(25%, -25%);"></button>
          `;
          previewContainer.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
      }
    });
  }

  previewContainer.addEventListener("click", (e) => {
    const closeBtn = e.target.closest(".btn-close");
    if (closeBtn) {
      const indexToRemove = parseInt(closeBtn.dataset.index);
      if (!isNaN(indexToRemove) && indexToRemove >= 0 && indexToRemove < selectedFiles.length) {
        selectedFiles.splice(indexToRemove, 1); // 從 selectedFiles 陣列中移除
      }
      renderPreviews();
    }
  });

  function validateForm() {
    let isValid = true;
    form.querySelectorAll('.form-text.text-danger').forEach(el => el.textContent = '');
    form.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
    imageError.textContent = '';

    form.querySelectorAll("input[required], select[required]").forEach(input => {
      const fieldName = input.name;
      const errorContainer = form.querySelector(`.${fieldName}_error`);

      if (!input.value.trim()) {
        if (errorContainer) {
          errorContainer.textContent = '此欄位為必填';
        } else if (input.nextElementSibling && input.nextElementSibling.classList.contains('form-text')) {
          input.nextElementSibling.textContent = '此欄位為必填';
        }
        input.classList.add('is-invalid');
        isValid = false;
      }

    });

    ["stock", "price", "weight"].forEach(id => {
      const input = document.getElementById(id);
      if (input && input.value) {
        const errorContainer = form.querySelector(`.${input.name}_error`);
        if (isNaN(input.value)) {
          if (errorContainer) errorContainer.textContent = '請輸入有效的數字';
          input.classList.add('is-invalid');
          isValid = false;
        } else {
          if (id === 'price' && Number(input.value) <= 0) {
            if (errorContainer) errorContainer.textContent = '單價必須大於0';
            input.classList.add('is-invalid');
            isValid = false;
          }
          if (id === 'stock' && Number(input.value) < 0) {
            if (errorContainer) errorContainer.textContent = '數量不可為負';
            input.classList.add('is-invalid');
            isValid = false;
          }
          if (id === 'weight' && Number(input.value) <= 0) {
            if (errorContainer) errorContainer.textContent = '重量必須大於0';
            input.classList.add('is-invalid');
            isValid = false;
          }
        }
      }
    });

    if (selectedFiles.length === 0) {
      imageError.textContent = '請至少上傳一張圖片';
      isValid = false;
    }
    return isValid;
  }

  const fieldsToValidateOnInput = form.querySelectorAll("input[required], select[required]");

  fieldsToValidateOnInput.forEach(input => {
    const eventType = input.tagName.toLowerCase() === 'select' ? 'change' : 'input';

    input.addEventListener(eventType, function() {
      const fieldName = this.name; // 獲取欄位的 name 屬性
      const errorContainer = form.querySelector(`.${fieldName}_error`); // 找到對應的錯誤訊息容器

      if (this.value.trim() !== '') {
        this.classList.remove('is-invalid');
        if (errorContainer) {
          errorContainer.textContent = '';
        }
      }

    });

    input.addEventListener('blur', function() {
      const fieldName = this.name;
      const errorContainer = form.querySelector(`.${fieldName}_error`);
      let specificFieldIsValid = true;

      if (!this.value.trim()) {
        this.classList.add('is-invalid');
        if (errorContainer) {
          errorContainer.textContent = '此欄位為必填';
        }
        specificFieldIsValid = false;
      } else {
        // 如果欄位有值了，先假設它是有效的，移除基本錯誤
        this.classList.remove('is-invalid');
        if (errorContainer) {
          errorContainer.textContent = '';
        }

        // 針對特定 ID 的欄位進行額外驗證
        if (this.id === 'price') {
          if (isNaN(this.value) || Number(this.value) <= 0) {
            this.classList.add('is-invalid');
            if (errorContainer) errorContainer.textContent = '單價必須是大於0的數字';
            specificFieldIsValid = false;
          }
        } else if (this.id === 'stock') {
          if (isNaN(this.value) || Number(this.value) < 0) {
            this.classList.add('is-invalid');
            if (errorContainer) errorContainer.textContent = '數量不可為負數';
            specificFieldIsValid = false;
          }
        } else if (this.id === 'weight') {
          if (isNaN(this.value) || Number(this.value) <= 0.0) { // 重量通常需要大於0
            this.classList.add('is-invalid');
            if (errorContainer) errorContainer.textContent = '重量必須是大於0的數字';
            specificFieldIsValid = false;
          }
        }
      }
      // --- 結束單一欄位驗證邏輯 ---

      // 如果經過所有檢查後，欄位仍然是有效的，確保錯誤樣式被移除
      if (specificFieldIsValid) {
        this.classList.remove('is-invalid');
        if (errorContainer) {
          errorContainer.textContent = '';
        }
      }
    });
  });

  imageInput.addEventListener("change", (event) => {
    const newFilesJustSelected = Array.from(event.target.files);

    newFilesJustSelected.forEach(newFile => {
      if (!selectedFiles.some(existingFile => existingFile.name === newFile.name && existingFile.size === newFile.size && existingFile.lastModified === newFile.lastModified)) {
        selectedFiles.push(newFile);
      }
    });

    renderPreviews();
    imageInput.value = "";

    // 當使用者選擇了檔案，就清除圖片相關的錯誤訊息
    if (selectedFiles.length > 0 && imageError) { // 加上 imageError 是否存在的檢查
      imageError.textContent = '';
    }
  });

  form.addEventListener("submit", async function(e) {
    e.preventDefault();
    const submitButton = form.querySelector('button[type="submit"]');

    if (!validateForm()) {
      const firstInvalidField = form.querySelector('.is-invalid');
      if (firstInvalidField) {
        firstInvalidField.focus();
      }
      return;
    }

    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 新增中...';

    const formData = new FormData();
    form.querySelectorAll("input[name], select[name]").forEach(input => {
      if (input.type === 'file') return;
      formData.append(input.name, input.value);
    });

    selectedFiles.forEach(file => {
      formData.append('productImages[]', file);
    });

    try {
      const response = await fetch('products_add-api.php', {
        method: 'POST',
        body: formData
      });
      const result = await response.json();

      if (result.success) {
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
        form.reset();
        previewContainer.innerHTML = '';
        selectedFiles = [];
        imageInput.value = '';
      } else {
        alert('新增失敗：' + (result.error || '未知錯誤'));
        if (result.postData) console.log('失敗時的提交資料:', result.postData);
        if (result.files) console.log('失敗時的檔案資料:', result.files);
      }
    } catch (err) {
      console.error('送出請求時發生錯誤:', err);
      alert('送出請求失敗，請檢查網路連線或稍後再試。');
    } finally {
      submitButton.disabled = false;
      submitButton.innerHTML = '新增商品';
    }
  });

  const addSportModalEl = document.getElementById('addSportModal');
  const addBrandModalEl = document.getElementById('addBrandModal');
  const addSportModal = addSportModalEl ? new bootstrap.Modal(addSportModalEl) : null;
  const addBrandModal = addBrandModalEl ? new bootstrap.Modal(addBrandModalEl) : null;


  const newSportNameInput = document.getElementById('newSportName');
  const newBrandNameInput = document.getElementById('newBrandName');
  const newSportError = document.getElementById('newSportError');
  const newBrandError = document.getElementById('newBrandError');

  if (document.getElementById('saveNewSport')) {
    document.getElementById('saveNewSport').addEventListener('click', async () => {
      const name = newSportNameInput.value.trim();
      const saveButton = document.getElementById('saveNewSport');
      newSportError.textContent = '';
      if (!name) {
        newSportError.textContent = '名稱不能為空';
        return;
      }
      saveButton.disabled = true;
      saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 儲存中...';

      try {
        const response = await fetch('sports_add-api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            name: name
          })
        });
        const result = await response.json();
        if (result.success && result.id && result.name) {
          const newOption = new Option(result.name, result.id, false, true);
          sportSelect.add(newOption);
          sportSelect.value = result.id;
          if (addSportModal) addSportModal.hide();
          newSportNameInput.value = '';
          if (sportErrorEl) sportErrorEl.textContent = '';
          sportSelect.classList.remove('is-invalid');
        } else {
          newSportError.textContent = result.error || '新增失敗';
        }
      } catch (err) {
        newSportError.textContent = '請求失敗，請稍後再試。';
        console.error('Error adding sport:', err);
      } finally {
        saveButton.disabled = false;
        saveButton.innerHTML = '儲存';
      }
    });
  }

  if (document.getElementById('saveNewBrand')) {
    document.getElementById('saveNewBrand').addEventListener('click', async () => {
      const name = newBrandNameInput.value.trim();
      const saveButton = document.getElementById('saveNewBrand');
      newBrandError.textContent = '';
      if (!name) {
        newBrandError.textContent = '名稱不能為空';
        return;
      }
      saveButton.disabled = true;
      saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 儲存中...';

      try {
        const response = await fetch('brands_add-api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            name: name
          })
        });
        const result = await response.json();
        if (result.success && result.id && result.name) {
          const newOption = new Option(result.name, result.id, false, true);
          brandSelect.add(newOption);
          brandSelect.value = result.id;
          if (addBrandModal) addBrandModal.hide();
          newBrandNameInput.value = '';
          if (brandErrorEl) brandErrorEl.textContent = '';
          brandSelect.classList.remove('is-invalid');
        } else {
          newBrandError.textContent = result.error || '新增失敗';
        }
      } catch (err) {
        newBrandError.textContent = '請求失敗，請稍後再試。';
        console.error('Error adding brand:', err);
      } finally {
        saveButton.disabled = false;
        saveButton.innerHTML = '儲存';
      }
    });
  }

  if (addSportModalEl) {
    addSportModalEl.addEventListener('hidden.bs.modal', () => {
      if (newSportError) newSportError.textContent = '';
      if (newSportNameInput) newSportNameInput.value = '';
    });
  }
  if (addBrandModalEl) {
    addBrandModalEl.addEventListener('hidden.bs.modal', () => {
      if (newBrandError) newBrandError.textContent = '';
      if (newBrandNameInput) newBrandNameInput.value = '';
    });
  }
</script>

<?php include __DIR__ . '/parts/html-tail.php' ?>