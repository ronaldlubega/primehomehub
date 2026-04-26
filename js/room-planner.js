// ===== ROOM PLANNER WITH HTML5 CANVAS =====

let roomPlanner = {
    canvas: null,
    ctx: null,
    room: { width: 400, height: 300, name: 'Living Room' },
    furniture: [],
    selectedFurniture: null,
    draggedFurniture: null,
    dragOffsetX: 0,
    dragOffsetY: 0,
    gridSize: 10,
    showGrid: true,
    snapToGrid: true,
    scale: 1,
    panX: 0,
    panY: 0
};

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('plannerCanvas')) {
        initRoomPlanner();
        setupRoomPlanner();
        if (typeof cart !== 'undefined') cart.updateBadge();
        if (typeof updateUserButton === 'function') updateUserButton();
    }
});

function initRoomPlanner() {
    const el = document.getElementById('plannerCanvas');
    if (!el) return;
    
    roomPlanner.canvas = el;
    roomPlanner.ctx = roomPlanner.canvas.getContext('2d');
    updateCanvasSize();

    roomPlanner.canvas.addEventListener('mousedown', handleCanvasMouseDown);
    roomPlanner.canvas.addEventListener('mousemove', handleCanvasMouseMove);
    roomPlanner.canvas.addEventListener('mouseup', handleCanvasMouseUp);
    roomPlanner.canvas.addEventListener('wheel', handleCanvasWheel);
    roomPlanner.canvas.addEventListener('touchstart', handleTouchStart);
    roomPlanner.canvas.addEventListener('touchmove', handleTouchMove);
    roomPlanner.canvas.addEventListener('touchend', handleTouchEnd);

    loadRoomFromStorage();
    drawCanvas();
}

function setupRoomPlanner() {
    document.getElementById('roomWidth').value = roomPlanner.room.width;
    document.getElementById('roomHeight').value = roomPlanner.room.height;
    document.getElementById('roomName').value = roomPlanner.room.name;

    document.getElementById('roomWidth').addEventListener('change', (e) => {
        roomPlanner.room.width = parseInt(e.target.value) || 400;
        drawCanvas();
    });

    document.getElementById('roomHeight').addEventListener('change', (e) => {
        roomPlanner.room.height = parseInt(e.target.value) || 300;
        drawCanvas();
    });

    document.getElementById('roomName').addEventListener('change', (e) => {
        roomPlanner.room.name = e.target.value;
    });

    document.getElementById('gridToggle').addEventListener('click', () => {
        roomPlanner.showGrid = !roomPlanner.showGrid;
        document.getElementById('gridToggle').classList.toggle('active');
        drawCanvas();
    });

    document.getElementById('snapToggle').addEventListener('click', () => {
        roomPlanner.snapToGrid = !roomPlanner.snapToGrid;
        document.getElementById('snapToggle').classList.toggle('active');
    });

    document.getElementById('zoomIn').addEventListener('click', () => {
        roomPlanner.scale = Math.min(roomPlanner.scale + 0.1, 3);
        drawCanvas();
    });

    document.getElementById('zoomOut').addEventListener('click', () => {
        roomPlanner.scale = Math.max(roomPlanner.scale - 0.1, 0.5);
        drawCanvas();
    });

    document.getElementById('resetZoom').addEventListener('click', () => {
        roomPlanner.scale = 1;
        roomPlanner.panX = 0;
        roomPlanner.panY = 0;
        drawCanvas();
    });

    document.getElementById('addSofaBtn').addEventListener('click', () => addFurniture('sofa', 100, 80));
    document.getElementById('addChairBtn').addEventListener('click', () => addFurniture('chair', 60, 60));
    document.getElementById('addTableBtn').addEventListener('click', () => addFurniture('table', 80, 80));
    document.getElementById('addDeskBtn').addEventListener('click', () => addFurniture('desk', 100, 50));
    document.getElementById('addBedBtn').addEventListener('click', () => addFurniture('bed', 120, 80));
    document.getElementById('addShelvingBtn').addEventListener('click', () => addFurniture('shelving', 120, 30));

    document.getElementById('clearPlanBtn').addEventListener('click', clearPlan);
    document.getElementById('savePlanBtn').addEventListener('click', savePlan);
    document.getElementById('downloadPlanBtn').addEventListener('click', downloadPlan);
    document.getElementById('deleteSelectedBtn').addEventListener('click', deleteSelected);
    document.getElementById('rotateLeftBtn').addEventListener('click', () => rotateFurniture(-15));
    document.getElementById('rotateRightBtn').addEventListener('click', () => rotateFurniture(15));

    updateFurnitureList();
}

function addFurniture(type, width, height) {
    const colors = { sofa: '#8b4513', chair: '#a0522d', table: '#cd853f', desk: '#daa520', bed: '#d2b48c', shelving: '#8b7355' };
    
    const furniture = {
        id: Date.now(),
        type, x: roomPlanner.room.width / 2,
        y: roomPlanner.room.height / 2,
        width, height, rotation: 0,
        color: colors[type] || '#999',
        name: type.charAt(0).toUpperCase() + type.slice(1)
    };

    roomPlanner.furniture.push(furniture);
    updateFurnitureList();
    drawCanvas();
}

function deleteFurniture(id) {
    roomPlanner.furniture = roomPlanner.furniture.filter(f => f.id !== id);
    if (roomPlanner.selectedFurniture?.id === id) roomPlanner.selectedFurniture = null;
    updateFurnitureList();
    drawCanvas();
}

function deleteSelected() {
    if (roomPlanner.selectedFurniture) {
        deleteFurniture(roomPlanner.selectedFurniture.id);
    }
}

function selectFurniture(id) {
    roomPlanner.selectedFurniture = roomPlanner.furniture.find(f => f.id === id) || null;
    updateFurnitureList();
    drawCanvas();
}

function rotateFurniture(angle) {
    if (!roomPlanner.selectedFurniture) return;
    roomPlanner.selectedFurniture.rotation = (roomPlanner.selectedFurniture.rotation + angle) % 360;
    drawCanvas();
}

function updateFurnitureList() {
    const container = document.getElementById('furnitureList');
    if (roomPlanner.furniture.length === 0) {
        container.innerHTML = '<li class="list-group-item text-muted text-center">Add furniture to get started</li>';
        return;
    }
    
    container.innerHTML = roomPlanner.furniture.map(f => `
        <li class="list-group-item d-flex justify-content-between ${roomPlanner.selectedFurniture?.id === f.id ? 'active' : ''}">
            <span onclick="selectFurniture(${f.id})" style="cursor: pointer; flex: 1;">
                <strong>${f.name}</strong>
                <small class="d-block text-muted">${f.width}×${f.height} | ${f.rotation}°</small>
            </span>
            <button onclick="deleteFurniture(${f.id})" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
        </li>
    `).join('');
}

function drawCanvas() {
    const canvas = roomPlanner.canvas;
    const ctx = roomPlanner.ctx;
    const width = canvas.width;
    const height = canvas.height;

    ctx.fillStyle = '#f8f9fa';
    ctx.fillRect(0, 0, width, height);

    ctx.save();
    ctx.translate(width / 2 + roomPlanner.panX, height / 2 + roomPlanner.panY);
    ctx.scale(roomPlanner.scale, roomPlanner.scale);
    ctx.translate(-roomPlanner.room.width / 2, -roomPlanner.room.height / 2);

    if (roomPlanner.showGrid) drawGrid();
    drawRoomWalls();
    roomPlanner.furniture.forEach(f => drawFurniture(f));
    if (roomPlanner.selectedFurniture) drawSelectionBox(roomPlanner.selectedFurniture);

    ctx.restore();
    drawInfoOverlay();
}

function drawGrid() {
    const ctx = roomPlanner.ctx;
    const size = roomPlanner.gridSize;
    const w = roomPlanner.room.width;
    const h = roomPlanner.room.height;
    
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 0.5;
    for (let x = 0; x <= w; x += size) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, h);
        ctx.stroke();
    }
    for (let y = 0; y <= h; y += size) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(w, y);
        ctx.stroke();
    }
}

function drawRoomWalls() {
    const ctx = roomPlanner.ctx;
    const w = roomPlanner.room.width;
    const h = roomPlanner.room.height;

    ctx.fillStyle = '#fff';
    ctx.strokeStyle = '#333';
    ctx.lineWidth = 3;
    ctx.fillRect(0, 0, w, h);
    ctx.strokeRect(0, 0, w, h);

    ctx.fillStyle = '#666';
    ctx.font = 'bold 12px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(roomPlanner.room.name, w / 2, h - 10);
}

function drawFurniture(furniture) {
    const ctx = roomPlanner.ctx;

    ctx.save();
    ctx.translate(furniture.x, furniture.y);
    ctx.rotate((furniture.rotation * Math.PI) / 180);

    ctx.fillStyle = furniture.color;
    ctx.strokeStyle = '#333';
    ctx.lineWidth = 2;
    ctx.fillRect(-furniture.width / 2, -furniture.height / 2, furniture.width, furniture.height);
    ctx.strokeRect(-furniture.width / 2, -furniture.height / 2, furniture.width, furniture.height);

    ctx.fillStyle = '#fff';
    ctx.font = '10px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(furniture.name, 0, 0);

    ctx.restore();
}

function drawSelectionBox(furniture) {
    const ctx = roomPlanner.ctx;

    ctx.save();
    ctx.translate(furniture.x, furniture.y);
    ctx.rotate((furniture.rotation * Math.PI) / 180);

    ctx.strokeStyle = '#667eea';
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);
    ctx.strokeRect(-furniture.width / 2 - 5, -furniture.height / 2 - 5, furniture.width + 10, furniture.height + 10);
    ctx.setLineDash([]);

    ctx.fillStyle = '#667eea';
    const handles = [
        [-furniture.width / 2 - 5, -furniture.height / 2 - 5],
        [furniture.width / 2 + 5, -furniture.height / 2 - 5],
        [furniture.width / 2 + 5, furniture.height / 2 + 5],
        [-furniture.width / 2 - 5, furniture.height / 2 + 5]
    ];
    handles.forEach(([x, y]) => ctx.fillRect(x - 3, y - 3, 6, 6));
    ctx.restore();
}

function drawInfoOverlay() {
    const ctx = roomPlanner.ctx;
    ctx.fillStyle = '#fff';
    ctx.font = '12px Arial';
    ctx.textAlign = 'left';
    ctx.fillText(`Furniture: ${roomPlanner.furniture.length}`, 10, 20);
    ctx.fillText(`Scale: ${(roomPlanner.scale * 100).toFixed(0)}%`, 10, 40);
    if (roomPlanner.selectedFurniture) ctx.fillText(`Selected: ${roomPlanner.selectedFurniture.name}`, 10, 60);
}

function handleCanvasMouseDown(e) {
    const rect = roomPlanner.canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const canvasX = (x - rect.width / 2 - roomPlanner.panX) / roomPlanner.scale + roomPlanner.room.width / 2;
    const canvasY = (y - rect.height / 2 - roomPlanner.panY) / roomPlanner.scale + roomPlanner.room.height / 2;

    for (let i = roomPlanner.furniture.length - 1; i >= 0; i--) {
        const f = roomPlanner.furniture[i];
        const dx = canvasX - f.x;
        const dy = canvasY - f.y;
        if (Math.abs(dx) < f.width / 2 && Math.abs(dy) < f.height / 2) {
            roomPlanner.draggedFurniture = f;
            roomPlanner.dragOffsetX = dx;
            roomPlanner.dragOffsetY = dy;
            selectFurniture(f.id);
            return;
        }
    }
    selectFurniture(null);
}

function handleCanvasMouseMove(e) {
    const rect = roomPlanner.canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const canvasX = (x - rect.width / 2 - roomPlanner.panX) / roomPlanner.scale + roomPlanner.room.width / 2;
    const canvasY = (y - rect.height / 2 - roomPlanner.panY) / roomPlanner.scale + roomPlanner.room.height / 2;

    if (roomPlanner.draggedFurniture) {
        let newX = canvasX - roomPlanner.dragOffsetX;
        let newY = canvasY - roomPlanner.dragOffsetY;

        if (roomPlanner.snapToGrid) {
            newX = Math.round(newX / roomPlanner.gridSize) * roomPlanner.gridSize;
            newY = Math.round(newY / roomPlanner.gridSize) * roomPlanner.gridSize;
        }

        newX = Math.max(roomPlanner.draggedFurniture.width / 2, Math.min(roomPlanner.room.width - roomPlanner.draggedFurniture.width / 2, newX));
        newY = Math.max(roomPlanner.draggedFurniture.height / 2, Math.min(roomPlanner.room.height - roomPlanner.draggedFurniture.height / 2, newY));

        roomPlanner.draggedFurniture.x = newX;
        roomPlanner.draggedFurniture.y = newY;
        drawCanvas();
    }
}

function handleCanvasMouseUp() {
    roomPlanner.draggedFurniture = null;
}

function handleCanvasWheel(e) {
    e.preventDefault();
    const factor = e.deltaY > 0 ? -0.1 : 0.1;
    roomPlanner.scale = Math.max(0.5, Math.min(3, roomPlanner.scale + factor));
    drawCanvas();
}

function handleTouchStart(e) {
    if (e.touches.length === 1) handleCanvasMouseDown(e.touches[0]);
}

function handleTouchMove(e) {
    if (e.touches.length === 1) {
        e.preventDefault();
        handleCanvasMouseMove(e.touches[0]);
    }
}

function handleTouchEnd(e) {
    handleCanvasMouseUp();
}

function updateCanvasSize() {
    const container = document.getElementById('canvasContainer');
    if (!container) return;
    roomPlanner.canvas.width = container.clientWidth;
    roomPlanner.canvas.height = container.clientHeight;
}

window.addEventListener('resize', () => {
    updateCanvasSize();
    drawCanvas();
});

function savePlan() {
    const plan = {
        name: roomPlanner.room.name,
        width: roomPlanner.room.width,
        height: roomPlanner.room.height,
        furniture: roomPlanner.furniture,
        timestamp: new Date().toISOString()
    };

    const plans = JSON.parse(localStorage.getItem('roomPlans')) || [];
    plans.push(plan);
    localStorage.setItem('roomPlans', JSON.stringify(plans));
    alert(`Plan saved: ${plan.name}`);
    loadSavedPlans();
}

function loadPlan() {
    const planName = prompt('Enter saved plan name:');
    if (!planName) return;

    const plans = JSON.parse(localStorage.getItem('roomPlans')) || [];
    const plan = plans.find(p => p.name === planName);

    if (plan) {
        roomPlanner.room = { width: plan.width, height: plan.height, name: plan.name };
        roomPlanner.furniture = plan.furniture;
        setupRoomPlanner();
        drawCanvas();
    } else {
        alert('Plan not found');
    }
}

function loadRoomFromStorage() {
    const saved = JSON.parse(localStorage.getItem('currentRoom'));
    if (saved) {
        roomPlanner.room = saved.room;
        roomPlanner.furniture = saved.furniture;
    }
}

function clearPlan() {
    if (confirm('Clear all furniture?')) {
        roomPlanner.furniture = [];
        roomPlanner.selectedFurniture = null;
        updateFurnitureList();
        drawCanvas();
    }
}

function downloadPlan() {
    const link = document.createElement('a');
    link.href = roomPlanner.canvas.toDataURL('image/png');
    link.download = `room-plan-${roomPlanner.room.name}-${Date.now()}.png`;
    link.click();
}

function loadSavedPlans() {
    const plans = JSON.parse(localStorage.getItem('roomPlans')) || [];
    const container = document.getElementById('savedPlans');
    if (!container) return;

    if (plans.length === 0) {
        container.innerHTML = '<small class="text-muted">No saved plans yet</small>';
        return;
    }

    container.innerHTML = plans.map((plan, i) => `
        <div class="badge bg-secondary me-2 mb-2">
            ${plan.name}
            <button onclick="deleteSavedPlan(${i})" style="background: none; border: none; color: white; cursor: pointer;">×</button>
        </div>
    `).join('');
}

function deleteSavedPlan(index) {
    const plans = JSON.parse(localStorage.getItem('roomPlans')) || [];
    plans.splice(index, 1);
    localStorage.setItem('roomPlans', JSON.stringify(plans));
    loadSavedPlans();
}
