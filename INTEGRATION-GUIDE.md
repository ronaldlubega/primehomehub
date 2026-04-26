# Quick Integration Guide

## 🔗 Navigation Updates

Update your main navigation in `index.html`, `shop.html`, and `visualizer.html`:

### Add to Navigation Bar:
```html
<li class="nav-item"><a class="nav-link" href="visualizer.html">Visualizer</a></li>
<li class="nav-item"><a class="nav-link" href="mood-boards.html">Mood Boards</a></li>
<li class="nav-item"><a class="nav-link" href="room-planner.html">Room Planner</a></li>
```

## 📂 File Structure

```
furn/
├── index.html                    (Main landing page)
├── shop.html                     (Product catalog)
├── visualizer.html               (Product visualizer)
├── room-planner.html            ✨ NEW
├── mood-boards.html             ✨ NEW
│
├── js/
│   ├── app.js                   (Main app logic)
│   ├── cart.js                  (Shopping cart)
│   ├── auth.js                  (Authentication)
│   ├── products.js              (Product data)
│   ├── room-planner.js          ✨ ENHANCED
│   ├── mood-boards.js           ✨ NEW
│   └── visualizer.js            (Visualizer logic)
│
├── css/
│   └── styles.css               (Main styles)
│
└── data/
    └── products.json            (Product database)
```

## 🚀 Getting Started

### 1. Verify Files Exist:
- ✅ `/room-planner.html` - Room designer interface
- ✅ `/mood-boards.html` - Mood board manager
- ✅ `/js/room-planner.js` - Room planner logic (enhanced)
- ✅ `/js/mood-boards.js` - Mood board logic (new)

### 2. Check Dependencies:
All required scripts are already loaded in the HTML files:
- Bootstrap 5.3.0
- Bootstrap Icons
- Custom styles

### 3. Update Navigation:
Ensure links in main pages point to:
- `room-planner.html`
- `mood-boards.html`

### 4. Test Both Features:
Open in browser:
- `http://localhost/furn/room-planner.html`
- `http://localhost/furn/mood-boards.html`

## 🎯 Key Features Summary

### Room Planner
```
✨ Features:
  • Canvas-based room designer
  • Add/remove furniture (Sofa, Chair, Table, Desk, Bed, Shelving)
  • Drag-and-drop positioning
  • 360° rotation controls
  • Grid snap alignment
  • Zoom/pan navigation
  • Save plans to browser
  • Export as PNG image
  • Real-time furniture list
```

### Mood Boards
```
✨ Features:
  • Create multiple design collections
  • Add products from shop catalog
  • Custom color palette builder
  • Design style tags (Modern, Minimalist, etc.)
  • Share via URL or Web Share API
  • Export as JSON file
  • Persistent localStorage
  • Grid visualization
```

## 💻 Browser Console Testing

### Test Room Planner:
```javascript
// Check if furniture added
console.log(roomPlanner.furniture);

// Check current scale
console.log(roomPlanner.scale);

// Check saved plans
console.log(localStorage.getItem('roomPlans'));
```

### Test Mood Boards:
```javascript
// Check all boards
console.log(boards);

// Check current board
console.log(currentBoard);

// Check saved boards in storage
console.log(JSON.parse(localStorage.getItem('moodBoards')));
```

## 🔧 Customization

### Room Dimensions:
Edit default values in `room-planner.js`:
```javascript
room: {
    width: 400,      // Change width default
    height: 300,     // Change height default
    name: 'Living Room'
}
```

### Furniture Types:
Add new furniture by calling:
```javascript
addFurniture('type', width, height);
// Example: addFurniture('chair', 60, 60);
```

### Design Styles:
Edit in `mood-boards.js`:
```javascript
const designStyles = ['Modern', 'Minimalist', 'Bohemian', ...];
```

## 🐛 Troubleshooting

### Canvas not rendering?
- Check if container `#canvasContainer` exists
- Verify Canvas API support in browser
- Check console for JavaScript errors

### Furniture not appearing?
- Ensure `furniture` array is populated
- Check if `drawCanvas()` is called
- Verify canvas size is > 0

### localStorage not working?
- Check browser privacy settings
- Ensure not in private/incognito mode
- Clear browser cache and try again

### Styles not loading?
- Verify `css/styles.css` path is correct
- Check if Bootstrap CSS loaded properly
- Inspect element for CSS conflicts

## 📊 Performance Tips

1. **Optimize Canvas Rendering:**
   - Avoid redrawing entire canvas every frame
   - Use dirty rectangles for updates only

2. **Manage Data:**
   - Limit furniture items (< 100 recommended)
   - Archive old designs periodically
   - Compress large saved plans

3. **Mobile Performance:**
   - Reduce canvas resolution on small screens
   - Disable animations if needed
   - Use touch optimization

## 🔐 Data Privacy

- All data stored locally in browser localStorage
- No data sent to server unless explicitly shared
- Sharing creates shareable URL with encoded data
- Export creates local files (not uploaded)

## 📝 Notes

- Both tools work offline (no internet required)
- Data persists until browser cache cleared
- Multiple boards/plans can be created
- Export formats: PNG (Room Planner), JSON (Mood Boards)

## ✅ Verification Steps

1. Open `/room-planner.html`
   - [ ] Canvas displays
   - [ ] Room controls work
   - [ ] Can add furniture
   - [ ] Can drag furniture
   - [ ] Can save/export

2. Open `/mood-boards.html`
   - [ ] Can create board
   - [ ] Can add products
   - [ ] Can add colors
   - [ ] Can add text
   - [ ] Can save/share

3. Check Navigation
   - [ ] All links work
   - [ ] No broken references
   - [ ] Proper page highlighting

## 🎉 Deployment Ready

Once verified, you can:
- Deploy to production server
- Update main website links
- Share with users
- Gather feedback

---

**Questions?** Check the main documentation at `ROOM-PLANNER-MOOD-BOARDS.md`
