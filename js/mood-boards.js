// ===== MOOD BOARDS COLLECTION MANAGER =====

let currentBoard = null;
let boards = [];

// Predefined design styles
const designStyles = ['Modern', 'Minimalist', 'Bohemian', 'Industrial', 'Scandinavian', 'Luxury', 'Rustic', 'Contemporary'];

// Predefined color palette
const colorPalette = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe', '#ff6b9d', '#ffa502', '#ffe66d'];

document.addEventListener('DOMContentLoaded', () => {
    loadBoards();
    setupEventListeners();
    cart.updateBadge();
    updateUserButton();
});

// Setup Event Listeners
function setupEventListeners() {
    document.getElementById('newBoardBtn').addEventListener('click', createNewBoard);
    document.getElementById('addProductBtn').addEventListener('click', showProductSelector);
    document.getElementById('addColorBtn').addEventListener('click', showColorPicker);
    document.getElementById('addTextBtn').addEventListener('click', addTextItem);
    document.getElementById('shareBtn').addEventListener('click', shareBoard);
    document.getElementById('renameBoardBtn').addEventListener('click', renameBoard);
    document.getElementById('deleteBoardBtn').addEventListener('click', deleteBoard);
    document.getElementById('downloadBoardBtn').addEventListener('click', downloadBoard);
    document.getElementById('addColorConfirmBtn').addEventListener('click', confirmAddColor);

    // Product selector
    document.getElementById('productSelector').addEventListener('click', (e) => {
        const product = e.target.closest('.selector-product');
        if (product) {
            addProductToBoard(JSON.parse(product.dataset.product));
            bootstrap.Modal.getInstance(document.getElementById('productSelectorModal')).hide();
        }
    });
}

// Load Boards
function loadBoards() {
    boards = JSON.parse(localStorage.getItem('moodBoards')) || [];
    renderBoardsList();
}

// Save Boards
function saveBoards() {
    localStorage.setItem('moodBoards', JSON.stringify(boards));
}

// Render Boards List
function renderBoardsList() {
    const container = document.getElementById('boardsList');
    
    if (boards.length === 0) {
        container.innerHTML = '<div class="text-muted text-center py-3">No mood boards yet</div>';
        return;
    }

    container.innerHTML = boards.map(board => `
        <div class="board-item ${currentBoard?.id === board.id ? 'active' : ''}" data-board-id="${board.id}">
            <h6>${board.name}</h6>
            <small>
                <span>${board.items.length} items</span>
                <span>${new Date(board.createdAt).toLocaleDateString()}</span>
            </small>
        </div>
    `).join('');

    document.querySelectorAll('.board-item').forEach(item => {
        item.addEventListener('click', () => {
            const boardId = item.dataset.boardId;
            selectBoard(boards.find(b => b.id === parseInt(boardId)));
        });
    });
}

// Create New Board
function createNewBoard() {
    const name = prompt('Board name:');
    if (!name) return;

    const newBoard = {
        id: Date.now(),
        name: name,
        createdAt: new Date().toISOString(),
        items: [],
        tags: [],
        styles: [],
        description: ''
    };

    boards.push(newBoard);
    saveBoards();
    selectBoard(newBoard);
    renderBoardsList();
}

// Select Board
function selectBoard(board) {
    currentBoard = board;
    renderBoardsList();
    renderBoardEditor();
    updateActionButtons();
}

// Render Board Editor
function renderBoardEditor() {
    if (!currentBoard) {
        document.getElementById('editor').style.display = 'none';
        document.getElementById('emptyState').style.display = 'flex';
        return;
    }

    document.getElementById('editor').style.display = 'block';
    document.getElementById('emptyState').style.display = 'none';

    // Update header
    document.getElementById('currentBoardName').textContent = currentBoard.name;
    document.getElementById('boardInfo').textContent = `${currentBoard.items.length} items • Created ${new Date(currentBoard.createdAt).toLocaleDateString()}`;

    // Render color tags
    renderColorTags();

    // Render style tags
    renderStyleTags();

    // Render mood grid
    renderMoodGrid();
}

// Render Color Tags
function renderColorTags() {
    const container = document.getElementById('colorTags');
    
    const defaultColors = [
        { name: 'Neutral', color: '#8b8b8b' },
        { name: 'Warm', color: '#d4a574' },
        { name: 'Cool', color: '#4facfe' },
        { name: 'Bold', color: '#ff6b9d' }
    ];

    container.innerHTML = defaultColors.map(tag => `
        <div class="color-tag" style="background: ${tag.color}30; color: ${tag.color}; border-color: ${tag.color};">
            ${tag.name}
        </div>
    `).join('') + currentBoard.tags.map(tag => `
        <div class="color-tag" style="background: ${tag.color}30; color: ${tag.color}; border-color: ${tag.color};">
            ${tag.name}
        </div>
    `).join('');
}

// Render Style Tags
function renderStyleTags() {
    const container = document.getElementById('styleTags');
    
    container.innerHTML = designStyles.map(style => `
        <div class="style-tag ${currentBoard.styles.includes(style) ? 'active' : ''}" data-style="${style}">
            ${style}
        </div>
    `).join('');

    document.querySelectorAll('.style-tag').forEach(tag => {
        tag.addEventListener('click', () => {
            const style = tag.dataset.style;
            if (currentBoard.styles.includes(style)) {
                currentBoard.styles = currentBoard.styles.filter(s => s !== style);
            } else {
                currentBoard.styles.push(style);
            }
            saveBoards();
            renderStyleTags();
        });
    });
}

// Render Mood Grid
function renderMoodGrid() {
    const container = document.getElementById('moodGrid');
    
    if (currentBoard.items.length === 0) {
        container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #999;">Add products or colors to start building your mood board</div>';
        return;
    }

    container.innerHTML = currentBoard.items.map((item, index) => {
        if (item.type === 'product') {
            return `
                <div class="mood-item" data-index="${index}">
                    <img src="${item.data.image}" alt="${item.data.name}">
                    <div class="mood-item-overlay">
                        <button onclick="removeFromBoard(${index})"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
        } else if (item.type === 'color') {
            return `
                <div class="mood-item" style="background: ${item.data.color};" data-index="${index}">
                    <div class="mood-item-overlay">
                        <button onclick="removeFromBoard(${index})"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
        } else if (item.type === 'text') {
            return `
                <div class="mood-item" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; word-wrap: break-word; padding: 1rem;" data-index="${index}">
                    <div>${item.data.text}</div>
                    <div class="mood-item-overlay">
                        <button onclick="removeFromBoard(${index})"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
        }
    }).join('');
}

// Show Product Selector
function showProductSelector() {
    const container = document.getElementById('productSelector');
    container.innerHTML = products.map(product => `
        <div class="selector-product" data-product='${JSON.stringify(product)}'>
            <img src="${product.image}" alt="${product.name}">
            <div class="selector-product-info">
                <h6>${product.name}</h6>
                <p>${product.category}</p>
                <p class="price">UG SHS ${product.price}</p>
            </div>
        </div>
    `).join('');

    new bootstrap.Modal(document.getElementById('productSelectorModal')).show();
}

// Add Product to Board
function addProductToBoard(product) {
    if (!currentBoard) return;

    currentBoard.items.push({
        type: 'product',
        data: product
    });

    saveBoards();
    renderMoodGrid();
}

// Show Color Picker
function showColorPicker() {
    new bootstrap.Modal(document.getElementById('colorPickerModal')).show();
}

// Confirm Add Color
function confirmAddColor() {
    const color = document.getElementById('colorInput').value;
    const name = document.getElementById('colorName').value || 'Color';

    if (!currentBoard) return;

    currentBoard.tags.push({
        name: name,
        color: color
    });

    currentBoard.items.push({
        type: 'color',
        data: { color: color, name: name }
    });

    saveBoards();
    renderColorTags();
    renderMoodGrid();

    // Reset and close modal
    document.getElementById('colorName').value = '';
    bootstrap.Modal.getInstance(document.getElementById('colorPickerModal')).hide();
}

// Add Text Item
function addTextItem() {
    const text = prompt('Enter text:');
    if (!text || !currentBoard) return;

    currentBoard.items.push({
        type: 'text',
        data: { text: text }
    });

    saveBoards();
    renderMoodGrid();
}

// Remove from Board
function removeFromBoard(index) {
    if (!currentBoard) return;
    currentBoard.items.splice(index, 1);
    saveBoards();
    renderMoodGrid();
}

// Share Board
function shareBoard() {
    if (!currentBoard) return;

    const boardData = encodeURIComponent(JSON.stringify({
        name: currentBoard.name,
        styles: currentBoard.styles,
        items: currentBoard.items
    }));

    const shareUrl = `${window.location.href}?board=${boardData}`;
    
    if (navigator.share) {
        navigator.share({
            title: currentBoard.name,
            text: 'Check out my design mood board!',
            url: shareUrl
        }).catch(err => console.log('Share failed'));
    } else {
        navigator.clipboard.writeText(shareUrl);
        alert('Board link copied to clipboard!');
    }
}

// Rename Board
function renameBoard() {
    if (!currentBoard) return;

    const newName = prompt('New board name:', currentBoard.name);
    if (newName && newName.trim()) {
        currentBoard.name = newName;
        saveBoards();
        renderBoardsList();
        renderBoardEditor();
    }
}

// Delete Board
function deleteBoard() {
    if (!currentBoard) return;

    if (confirm(`Delete "${currentBoard.name}"? This cannot be undone.`)) {
        boards = boards.filter(b => b.id !== currentBoard.id);
        currentBoard = null;
        saveBoards();
        renderBoardsList();
        document.getElementById('editor').style.display = 'none';
        document.getElementById('emptyState').style.display = 'flex';
    }
}

// Download Board
function downloadBoard() {
    if (!currentBoard) return;

    const data = {
        name: currentBoard.name,
        styles: currentBoard.styles,
        items: currentBoard.items.map(item => ({
            type: item.type,
            data: item.type === 'product' ? {
                id: item.data.id,
                name: item.data.name,
                price: item.data.price
            } : item.data
        }))
    };

    const json = JSON.stringify(data, null, 2);
    const blob = new Blob([json], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `mood-board-${currentBoard.name}-${Date.now()}.json`;
    link.click();
}

// Update Action Buttons
function updateActionButtons() {
    const hasBoard = currentBoard !== null;
    document.getElementById('renameBoardBtn').style.display = hasBoard ? 'flex' : 'none';
    document.getElementById('deleteBoardBtn').style.display = hasBoard ? 'flex' : 'none';
    document.getElementById('downloadBoardBtn').style.display = hasBoard ? 'flex' : 'none';
}
