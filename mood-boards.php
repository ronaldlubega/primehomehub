<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

// Get database connection
$db = new Database();

// Get mood boards
$moodBoards = $db->fetch_all("SELECT * FROM mood_boards ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood Boards - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .mood-board-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .mood-board-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .mood-board-card:hover {
            transform: translateY(-5px);
        }
        .mood-board-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        .mood-board-content {
            padding: 15px;
        }
        .color-palette {
            display: flex;
            gap: 5px;
            margin: 10px 0;
        }
        .color-swatch {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #dee2e6;
        }
        .create-board {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .create-board:hover {
            border-color: #667eea;
            background: #e3f2fd;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Mood Boards</h1>
            <p class="lead">Create and explore design inspiration</p>
        </div>
    </section>

    <!-- Mood Boards Content -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>My Mood Boards</h3>
                <button class="btn btn-primary" onclick="createNewBoard()">
                    <i class="bi bi-plus-circle"></i> Create New Board
                </button>
            </div>
            
            <div class="mood-board-grid">
                <!-- Create New Board Card -->
                <div class="create-board" onclick="createNewBoard()">
                    <div class="text-center">
                        <i class="bi bi-plus-circle display-1 text-muted"></i>
                        <h5 class="mt-3">Create New Board</h5>
                        <p class="text-muted">Start a new design project</p>
                    </div>
                </div>
                
                <!-- Existing Mood Boards -->
                <?php if (empty($moodBoards)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-palette display-1 text-muted"></i>
                            <h3 class="mt-3">No Mood Boards Yet</h3>
                            <p class="text-muted">Create your first mood board to get started!</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($moodBoards as $board): ?>
                        <div class="mood-board-card" onclick="viewBoard(<?php echo $board['id']; ?>)">
                            <div class="mood-board-image">
                                <?php if ($board['image_url']): ?>
                                    <img src="<?php echo $board['image_url']; ?>" alt="<?php echo htmlspecialchars($board['title']); ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="bi bi-palette"></i>
                                <?php endif; ?>
                            </div>
                            <div class="mood-board-content">
                                <h5><?php echo htmlspecialchars($board['title']); ?></h5>
                                <p class="text-muted small"><?php echo htmlspecialchars(substr($board['description'], 0, 100)); ?>...</p>
                                
                                <?php if ($board['colors']): ?>
                                    <div class="color-palette">
                                        <?php $colors = json_decode($board['colors'], true) ?: []; ?>
                                        <?php foreach (array_slice($colors, 0, 5) as $color): ?>
                                            <div class="color-swatch" style="background-color: <?php echo $color; ?>;"></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($board['created_at'])); ?></small>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); editBoard(<?php echo $board['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteBoard(<?php echo $board['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Create/Edit Board Modal -->
    <div class="modal fade" id="boardModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="boardModalTitle">Create Mood Board</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="boardForm">
                        <div class="mb-3">
                            <label for="boardTitle" class="form-label">Board Title</label>
                            <input type="text" class="form-control" id="boardTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="boardDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="boardDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="boardStyle" class="form-label">Style</label>
                            <select class="form-select" id="boardStyle">
                                <option value="modern">Modern</option>
                                <option value="traditional">Traditional</option>
                                <option value="minimalist">Minimalist</option>
                                <option value="industrial">Industrial</option>
                                <option value="scandinavian">Scandinavian</option>
                                <option value="bohemian">Bohemian</option>
                                <option value="coastal">Coastal</option>
                                <option value="farmhouse">Farmhouse</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="boardColors" class="form-label">Color Palette</label>
                            <div class="d-flex gap-2 mb-2">
                                <input type="color" class="form-control form-control-color" id="color1" value="#667eea">
                                <input type="color" class="form-control form-control-color" id="color2" value="#764ba2">
                                <input type="color" class="form-control form-control-color" id="color3" value="#f093fb">
                                <input type="color" class="form-control form-control-color" id="color4" value="#4facfe">
                                <input type="color" class="form-control form-control-color" id="color5" value="#43e97b">
                            </div>
                            <small class="text-muted">Choose 5 colors for your palette</small>
                        </div>
                        <div class="mb-3">
                            <label for="boardImage" class="form-label">Cover Image URL (optional)</label>
                            <input type="url" class="form-control" id="boardImage" placeholder="https://example.com/image.jpg">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveBoard()">Save Board</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Board Modal -->
    <div class="modal fade" id="viewBoardModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewBoardTitle">Mood Board Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewBoardContent"></div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/navigation.js"></script>
    <script>
        let currentBoardId = null;
        
        function createNewBoard() {
            currentBoardId = null;
            document.getElementById('boardModalTitle').textContent = 'Create Mood Board';
            document.getElementById('boardForm').reset();
            
            const modal = new bootstrap.Modal(document.getElementById('boardModal'));
            modal.show();
        }
        
        function editBoard(boardId) {
            currentBoardId = boardId;
            document.getElementById('boardModalTitle').textContent = 'Edit Mood Board';
            
            // In a real application, this would fetch board data from the server
            document.getElementById('boardTitle').value = 'Board ' + boardId;
            document.getElementById('boardDescription').value = 'Sample description for board ' + boardId;
            
            const modal = new bootstrap.Modal(document.getElementById('boardModal'));
            modal.show();
        }
        
        function viewBoard(boardId) {
            // In a real application, this would fetch detailed board data
            document.getElementById('viewBoardTitle').textContent = 'Mood Board Details';
            document.getElementById('viewBoardContent').innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <div class="text-center">
                            <div class="bg-light rounded p-5" style="min-height: 400px;">
                                <i class="bi bi-palette display-1 text-muted"></i>
                                <h4 class="mt-3">Board Content</h4>
                                <p class="text-muted">Detailed mood board content would be displayed here.</p>
                                <p>Board ID: ${boardId}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h5>Board Information</h5>
                        <div class="mb-3">
                            <strong>Style:</strong> Modern
                        </div>
                        <div class="mb-3">
                            <strong>Created:</strong> ${new Date().toLocaleDateString()}
                        </div>
                        <div class="mb-3">
                            <strong>Colors:</strong>
                            <div class="d-flex gap-2 mt-2">
                                <div class="color-swatch" style="background-color: #667eea;"></div>
                                <div class="color-swatch" style="background-color: #764ba2;"></div>
                                <div class="color-swatch" style="background-color: #f093fb;"></div>
                                <div class="color-swatch" style="background-color: #4facfe;"></div>
                                <div class="color-swatch" style="background-color: #43e97b;"></div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="editBoard(${boardId})">Edit Board</button>
                            <button class="btn btn-outline-success">Share Board</button>
                            <button class="btn btn-outline-info">Export PDF</button>
                        </div>
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('viewBoardModal'));
            modal.show();
        }
        
        function deleteBoard(boardId) {
            if (confirm('Are you sure you want to delete this mood board?')) {
                // In a real application, this would send a delete request to the server
                console.log('Deleting board:', boardId);
                alert('Mood board deleted successfully!');
                location.reload();
            }
        }
        
        function saveBoard() {
            const title = document.getElementById('boardTitle').value;
            const description = document.getElementById('boardDescription').value;
            const style = document.getElementById('boardStyle').value;
            const image = document.getElementById('boardImage').value;
            
            // Get colors
            const colors = [
                document.getElementById('color1').value,
                document.getElementById('color2').value,
                document.getElementById('color3').value,
                document.getElementById('color4').value,
                document.getElementById('color5').value
            ];
            
            if (!title) {
                alert('Please enter a board title');
                return;
            }
            
            // In a real application, this would save to the database
            const boardData = {
                id: currentBoardId,
                title: title,
                description: description,
                style: style,
                colors: colors,
                image_url: image
            };
            
            console.log('Saving board:', boardData);
            alert('Mood board saved successfully!');
            
            // Close modal and reload
            bootstrap.Modal.getInstance(document.getElementById('boardModal')).hide();
            location.reload();
        }
    </script>
</body>
</html>
