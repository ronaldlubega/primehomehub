<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=visualizer');
    exit();
}

// Get database connection
$db = new Database();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Visualizer - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .visualizer-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            min-height: 600px;
        }
        .room-canvas {
            background: white;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .furniture-item {
            position: absolute;
            cursor: move;
            user-select: none;
            padding: 10px;
            background: rgba(102, 126, 234, 0.1);
            border: 2px solid #667eea;
            border-radius: 8px;
        }
        .furniture-palette {
            background: white;
            border-radius: 8px;
            padding: 15px;
            max-height: 500px;
            overflow-y: auto;
        }
        .furniture-option {
            cursor: pointer;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .furniture-option:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Room Visualizer</h1>
            <p class="lead">Design your dream room with our interactive visualizer</p>
        </div>
    </section>

    <!-- Visualizer Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="visualizer-container">
                        <h4 class="mb-3">Room Canvas</h4>
                        <div class="room-canvas" id="roomCanvas">
                            <div class="text-center text-muted">
                                <i class="bi bi-house display-1"></i>
                                <p class="mt-3">Drag and drop furniture items here</p>
                            </div>
                        </div>
                        
                        <div class="mt-3 d-flex gap-2">
                            <button class="btn btn-outline-danger" onclick="clearCanvas()">
                                <i class="bi bi-trash"></i> Clear Canvas
                            </button>
                            <button class="btn btn-outline-primary" onclick="saveDesign()">
                                <i class="bi bi-save"></i> Save Design
                            </button>
                            <button class="btn btn-outline-success" onclick="shareDesign()">
                                <i class="bi bi-share"></i> Share Design
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="furniture-palette">
                        <h4 class="mb-3">Furniture Palette</h4>
                        
                        <div class="mb-4">
                            <h6>Living Room</h6>
                            <div class="furniture-option" draggable="true" data-furniture="sofa">
                                <i class="bi bi-lamp"></i> Sofa
                            </div>
                            <div class="furniture-option" draggable="true" data-furniture="table">
                                <i class="bi bi-square"></i> Coffee Table
                            </div>
                            <div class="furniture-option" draggable="true" data-furniture="chair">
                                <i class="bi bi-square"></i> Armchair
                            </div>
                            <div class="furniture-option" draggable="true" data-furniture="tv">
                                <i class="bi bi-tv"></i> TV Stand
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6>Bedroom</h6>
                            <div class="furniture-option" draggable="true" data-furniture="bed">
                                <i class="bi bi-square"></i> Bed
                            </div>
                            <div class="furniture-option" draggable="true" data-furniture="wardrobe">
                                <i class="bi bi-door-closed"></i> Wardrobe
                            </div>
                            <div class="furniture-option" draggable="true" data-furniture="desk">
                                <i class="bi bi-square"></i> Desk
                            </div>
                            <div class="furniture-option" draggable="true" data-furniture="dresser">
                                <i class="bi bi-square"></i> Dresser
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6>Kitchen</h6>
                            <div class="furniture-option" draggable="true" data-furniture="cabinet">
                                <i class="bi bi-door-closed"></i> Cabinet
                            </div>
                            <div class="furniture-option" draggable="true" data-furniture="refrigerator">
                                <i class="bi bi-square"></i> Refrigerator
                            </div>
                            <div class="furniture-option" draggable="true" data-furniture="stove">
                                <i class="bi bi-square"></i> Stove
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/navigation.js"></script>
    <script>
        let draggedElement = null;
        let furnitureCounter = 0;
        
        // Initialize drag and drop
        document.addEventListener('DOMContentLoaded', function() {
            const furnitureOptions = document.querySelectorAll('.furniture-option');
            const roomCanvas = document.getElementById('roomCanvas');
            
            furnitureOptions.forEach(option => {
                option.addEventListener('dragstart', handleDragStart);
                option.addEventListener('dragend', handleDragEnd);
            });
            
            roomCanvas.addEventListener('dragover', handleDragOver);
            roomCanvas.addEventListener('drop', handleDrop);
        });
        
        function handleDragStart(e) {
            draggedElement = e.target;
            e.target.style.opacity = '0.5';
        }
        
        function handleDragEnd(e) {
            e.target.style.opacity = '';
        }
        
        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        }
        
        function handleDrop(e) {
            e.preventDefault();
            
            if (draggedElement) {
                const canvas = document.getElementById('roomCanvas');
                const rect = canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // Create furniture item
                const furnitureItem = document.createElement('div');
                furnitureItem.className = 'furniture-item';
                furnitureItem.innerHTML = draggedElement.innerHTML;
                furnitureItem.style.left = x + 'px';
                furnitureItem.style.top = y + 'px';
                furnitureItem.dataset.furnitureId = 'furniture-' + (++furnitureCounter);
                
                // Make it draggable within canvas
                makeDraggable(furnitureItem);
                
                // Add to canvas
                canvas.appendChild(furnitureItem);
                
                // Clear placeholder if exists
                const placeholder = canvas.querySelector('.text-center');
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            }
        }
        
        function makeDraggable(element) {
            let isDragging = false;
            let startX, startY, initialX, initialY;
            
            element.addEventListener('mousedown', function(e) {
                isDragging = true;
                startX = e.clientX;
                startY = e.clientY;
                initialX = element.offsetLeft;
                initialY = element.offsetTop;
                element.style.zIndex = 1000;
            });
            
            document.addEventListener('mousemove', function(e) {
                if (isDragging) {
                    const dx = e.clientX - startX;
                    const dy = e.clientY - startY;
                    element.style.left = (initialX + dx) + 'px';
                    element.style.top = (initialY + dy) + 'px';
                }
            });
            
            document.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    element.style.zIndex = '';
                }
            });
        }
        
        function clearCanvas() {
            const canvas = document.getElementById('roomCanvas');
            const furnitureItems = canvas.querySelectorAll('.furniture-item');
            furnitureItems.forEach(item => item.remove());
            
            // Show placeholder again
            const placeholder = canvas.querySelector('.text-center');
            if (placeholder) {
                placeholder.style.display = 'block';
            }
        }
        
        function saveDesign() {
            const canvas = document.getElementById('roomCanvas');
            const furnitureItems = canvas.querySelectorAll('.furniture-item');
            
            if (furnitureItems.length === 0) {
                alert('Please add some furniture to the canvas first');
                return;
            }
            
            // Collect furniture positions
            const designData = [];
            furnitureItems.forEach(item => {
                designData.push({
                    type: item.dataset.furnitureId,
                    left: item.style.left,
                    top: item.style.top,
                    content: item.innerHTML
                });
            });
            
            // In a real application, this would save to the database
            console.log('Saving design:', designData);
            alert('Design saved successfully!');
        }
        
        function shareDesign() {
            const canvas = document.getElementById('roomCanvas');
            const furnitureItems = canvas.querySelectorAll('.furniture-item');
            
            if (furnitureItems.length === 0) {
                alert('Please add some furniture to the canvas first');
                return;
            }
            
            // In a real application, this would generate a shareable link
            const shareUrl = window.location.href + '?design=' + Math.random().toString(36).substr(2, 9);
            
            if (navigator.share) {
                navigator.share({
                    title: 'My Room Design',
                    text: 'Check out my room design!',
                    url: shareUrl
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(shareUrl).then(() => {
                    alert('Share link copied to clipboard!');
                });
            }
        }
    </script>
</body>
</html>
