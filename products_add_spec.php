<?php
include __DIR__ . '/parts/init.php';

$title = '新增商品規格';
$pageName = 'products_add_spec';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$product_stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$product_stmt->execute([$product_id]);
$product = $product_stmt->fetch();

if (!$product) {
    header("Location: products_list.php");
    exit;
}

$existing_specs_stmt = $pdo->prepare("SELECT * FROM specs WHERE product_id = ? ORDER BY spec_id ASC");
$existing_specs_stmt->execute([$product_id]);
$existing_specs = $existing_specs_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<div class="container">
    <div class="row mb-4 mt-3">
    </div>
    <?php if (!empty($existing_specs)): ?>
        <div class="row mb-4">
            <div class="col-1 d-none d-md-block"></div>
            <div class="col-12 col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">目前已有規格</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>款式</th>
                                        <th>單價</th>
                                        <th>數量</th>
                                        <th>材質(公克)</th>
                                        <th>尺寸(公分)</th>
                                        <th>重量</th>
                                        <th>產地</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($existing_specs as $index => $spec): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlentities($spec['color']) ?></td>
                                            <td><?= htmlentities($spec['price']) ?></td>
                                            <td><?= htmlentities($spec['stock']) ?></td>
                                            <td><?= htmlentities($spec['material']) ?></td>
                                            <td><?= htmlentities($spec['size']) ?></td>
                                            <td><?= htmlentities($spec['weight']) ?></td>
                                            <td><?= htmlentities($spec['origin']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-1 d-none d-md-block"></div>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col-1 d-none d-md-block"></div>
            <div class="col-12 col-md-10">
                <div class="alert alert-info" role="alert">
                    商品 "<?= htmlentities($product['name']) ?>" 目前尚無任何已存在的規格。
                </div>
            </div>
            <div class="col-1 d-none d-md-block"></div>
        </div>
    <?php endif; ?>

    <form id="specForm" method="post" action="products_add_spec-api.php" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">
        <div class="row">
            <div class="col-1 d-none d-md-block"></div>
            <div class="col-12 col-md-10">
                <div class="row mb-1">
                    <div class="col-12">
                        <h5 class="mb-3 border-bottom pb-2">填寫新規格內容</h5>
                    </div>
                    <div class="col-md-7 col-lg-8 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="price" class="form-label">單價</label>
                                    <input type="text" class="form-control" id="price" name="price">
                                    <div class="form-text text-danger"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="stock" class="form-label">數量</label>
                                    <input type="text" class="form-control" id="stock" name="stock">
                                    <div class="form-text text-danger"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="material" class="form-label">材質</label>
                                    <input type="text" class="form-control" id="material" name="material">
                                    <div class="form-text text-danger"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="size" class="form-label">尺寸(單位：公分)</label>
                                    <input type="text" class="form-control" id="size" name="size">
                                    <div class="form-text text-danger"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="weight" class="form-label">重量(單位：公克)</label>
                                    <input type="text" class="form-control" id="weight" name="weight">
                                    <div class="form-text text-danger"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="color" class="form-label">款式 (例如：顏色、型號)</label>
                                    <input type="text" class="form-control" id="color" name="color">
                                    <div class="form-text text-danger"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="origin" class="form-label">產地</label>
                                    <input type="text" class="form-control" id="origin" name="origin">
                                    <div class="form-text text-danger"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">商品圖片</h5>
                                <input type="file" id="productImages" name="productImages[]" multiple accept="image/*" style="display: none;">
                                <label for="productImages" class="btn btn-success btn-sm text-light mb-2">
                                    <i class="fa-solid fa-image me-1"></i> 選擇圖片
                                </label>
                                <div id="previewContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                                <div id="imageError" class="form-text text-danger mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-10 mb-5">
                    <button type="submit" class="btn btn-primary">新增</button>
                    <a href="javascript:void(0);" onclick="history.back(); return false;" class="btn btn-outline-secondary ms-2">取消</a>
                </div>
            </div>
            <div class="col-1 d-none d-md-block"></div>
        </div>
    </form>
</div>
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">新增成功</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                規格已成功新增！
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="successModalContinueButton" data-bs-dismiss="modal">繼續新增</button>
                <a href="products_list.php" class="btn btn-primary" id="successModalToListButton">回商品列表頁</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script>
    const imageInput = document.getElementById("productImages");
    const previewContainer = document.getElementById("previewContainer");
    const imageErrorDiv = document.getElementById("imageError");
    let selectedFiles = [];

    imageInput.addEventListener("change", () => {
        const newFiles = Array.from(imageInput.files);
        newFiles.forEach(file => {
            if (!selectedFiles.some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified)) {
                selectedFiles.push(file);
            }
        });
        renderPreviews();
        imageInput.value = "";

        if (selectedFiles.length > 0 && imageErrorDiv) {
            imageErrorDiv.innerText = "";
        }
    });

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
                        <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #ddd; border-radius: .25rem;" alt="預覽 ${file.name}">
                        <button type="button" class="btn-close btn-sm position-absolute top-0 end-0 bg-light p-1" data-index="${index}" aria-label="移除此圖片" style="transform: translate(25%, -25%);"></button>
                    `;
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    previewContainer.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-close");
        if (btn) {
            const index = parseInt(btn.dataset.index);
            if (!isNaN(index) && index >= 0 && index < selectedFiles.length) {
                selectedFiles.splice(index, 1);
                renderPreviews();
            }
        }
    });

    const specForm = document.getElementById("specForm");
    const specSubmitButton = specForm.querySelector('button[type="submit"]');
    const requiredFieldIDs = ["price", "stock", "material", "size", "weight", "color", "origin"];

    requiredFieldIDs.forEach(id => {
        const input = specForm.querySelector(`#${id}`);
        if (!input) return;

        const eventType = input.tagName.toLowerCase() === 'select' ? 'change' : 'input';
        input.addEventListener(eventType, function() {
            validateField(this, true);
        });

        input.addEventListener('blur', function() {
            validateField(this, false);
        });
    });


    function validateField(field, isRealtime = false) {
        let errorContainer = field.nextElementSibling;
        let isValidForErrorContainer = errorContainer && errorContainer.classList.contains('form-text') && errorContainer.classList.contains('text-danger');
        let errorMessage = '';
        let hasError = false;

        if (requiredFieldIDs.includes(field.id) && field.value.trim() === "") {
            errorMessage = '此欄位為必填';
            hasError = true;
        } else {
            if (field.id === 'price' || field.id === 'weight') {
                if (isNaN(parseFloat(field.value)) || parseFloat(field.value) <= 0) {
                    errorMessage = field.id === 'price' ? '單價必須是大於0的數字' : '重量必須是大於0的數字';
                    hasError = true;
                }
            } else if (field.id === 'stock') {
                if (isNaN(parseInt(field.value)) || parseInt(field.value) < 0) {
                    errorMessage = '數量不可為負數';
                    hasError = true;
                }
            }
        }

        if (hasError) {
            field.classList.add('is-invalid');
            if (isValidForErrorContainer) {
                errorContainer.textContent = errorMessage;
            }
        } else {
            field.classList.remove('is-invalid');
            if (isValidForErrorContainer) {
                errorContainer.textContent = '';
            }
        }
        return !hasError;
    }

    specForm.addEventListener("submit", async function(e) {
        e.preventDefault();
        let isPass = true;

        specForm.querySelectorAll('.form-text.text-danger').forEach(el => el.textContent = '');
        specForm.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));

        requiredFieldIDs.forEach(id => {
            const field = specForm.querySelector(`#${id}`);
            if (field) {
                if (!validateField(field, false)) {
                    isPass = false;
                }
            }
        });

        if (imageErrorDiv) imageErrorDiv.innerText = "";
        if (selectedFiles.length === 0) {
            isPass = false;
            if (imageErrorDiv) imageErrorDiv.innerText = "請至少上傳一張圖片";
        }

        if (!isPass) {
            const firstInvalidField = specForm.querySelector('.is-invalid');
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
            return;
        }

        if (specSubmitButton) {
            specSubmitButton.disabled = true;
            specSubmitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 新增中...';
        }

        const formData = new FormData(specForm);
        if (selectedFiles.length > 0) {
            selectedFiles.forEach(file => {
                formData.append('productImages[]', file);
            });
        }

        try {
            const response = await fetch('products_add_spec-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                const successModalEl = document.getElementById('successModal');
                const modalInstance = new bootstrap.Modal(successModalEl);
                modalInstance.show();

                specForm.reset();
                if (typeof previewContainer !== 'undefined') previewContainer.innerHTML = '';
                selectedFiles = [];
                if (typeof imageInput !== 'undefined') imageInput.value = '';

                const continueButton = document.getElementById('successModalContinueButton');
                successModalEl.addEventListener('hidden.bs.modal', function onModalHidden() {
                    location.reload();
                    successModalEl.removeEventListener('hidden.bs.modal', onModalHidden);
                });


            } else {
                let userMessage = '新增規格失敗。';
                if (result.error) {
                    userMessage += '原因：' + result.error;
                } else if (result.errors) {
                    userMessage += '請檢查以下欄位：\n';
                    for (const key in result.errors) {
                        userMessage += `${key}: ${result.errors[key]}\n`;
                        const field = specForm.querySelector(`#${key}`);
                        if (field) {
                            field.classList.add('is-invalid');
                            const errorContainer = field.nextElementSibling;
                            if (errorContainer && errorContainer.classList.contains('form-text') && errorContainer.classList.contains('text-danger')) {
                                errorContainer.textContent = result.errors[key];
                            }
                        }
                    }
                } else {
                    userMessage += '請稍後再試。';
                    console.error('API錯誤:', result);
                }
                alert(userMessage);
            }
        } catch (err) {
            console.error('新增規格請求時發生錯誤:', err);
            alert('請求無法完成，請檢查您的網路連線或稍後再試。');
        } finally {
            if (specSubmitButton) {
                specSubmitButton.disabled = false;
                specSubmitButton.innerHTML = '新增此規格';
            }
        }
    });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>