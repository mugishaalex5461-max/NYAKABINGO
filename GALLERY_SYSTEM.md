# Gallery System - Complete Setup Guide

## Overview
The NYAKABINGO PRIMARY SCHOOL website now has a fully functional image gallery system with drag-and-drop upload capabilities.

## Components

### 1. **gallery.php** (Frontend)
- Location: `pages/gallery.php`
- Features:
  - Upload form with drag-and-drop support
  - Automatic image display
  - Lightbox viewer for full-size images
  - Category information
  - Responsive grid layout

### 2. **upload_images.php** (Backend)
- Location: `pages/upload_images.php`
- Features:
  - Handles POST requests for image uploads
  - Validates file type (JPEG, PNG, GIF, WebP)
  - Validates file size (max 5MB)
  - Auto-generates unique filenames
  - Creates thumbnail versions automatically
  - Returns JSON response with upload status

### 3. **upload.js** (JavaScript)
- Location: `js/upload.js`
- Features:
  - Drag and drop functionality
  - File input handling
  - Image preview before upload
  - Progress feedback
  - Error handling
  - Auto-reload on successful upload

### 4. **images/uploads/** (Storage)
- Location: `images/uploads/`
- Stores:
  - Original uploaded images
  - Auto-generated thumbnails (prefixed with `thumb_`)

## How to Use

### Uploading Images

**Method 1: Drag and Drop**
1. Go to Gallery page (`http://localhost/NYAKABINGO_PRIMARY/pages/gallery.php`)
2. Locate "Upload Photos to Gallery" section
3. Drag image files directly onto the drag-drop zone
4. Click "Upload Image" button

**Method 2: File Selection**
1. Go to Gallery page
2. Click anywhere on the drag-drop zone
3. Select an image file from your computer
4. Click "Upload Image" button

### Supported Features

✓ Upload single or multiple images (one at a time)
✓ Drag and drop support
✓ Image preview before upload
✓ Progress feedback
✓ Error messages for invalid files
✓ Automatic thumbnail generation
✓ Lightbox viewer (click image to enlarge)
✓ Image upload date display

### File Requirements

- **Formats**: JPG, PNG, GIF, WebP
- **Size**: Max 5MB per file
- **Dimensions**: No restrictions (auto-resized for thumbnails)

## Technology Stack

- **Backend**: PHP with GD Library (for thumbnail generation)
- **Frontend**: Vanilla JavaScript (no dependencies)
- **Storage**: Server file system
- **Format**: JSON responses

## File Structure

```
NYAKABINGO_PRIMARY/
├── pages/
│   ├── gallery.php ..................... Main gallery page
│   └── upload_images.php ............... Upload handler
├── js/
│   └── upload.js ....................... Upload functionality
└── images/
    └── uploads/ ........................ Image storage
        ├── IMG_*.jpg/png/gif/webp ..... Uploaded images
        ├── thumb_IMG_*.jpg/png/... .... Thumbnails
        └── README.md ................... Documentation
```

## Testing the Gallery

1. **Test Upload:**
   - Navigate to `http://localhost/NYAKABINGO_PRIMARY/pages/gallery.php`
   - Upload a test image
   - Verify it appears in the gallery grid

2. **Test Lightbox:**
   - Click on any gallery image
   - Full-size image should display in lightbox
   - Click ✕ or outside image to close

3. **Test Validation:**
   - Try uploading file larger than 5MB (should fail)
   - Try uploading non-image file (should fail)
   - Try uploading valid image (should succeed)

## Performance Notes

- Thumbnails are 300x300px (optmized for web)
- Original images are preserved
- Auto-generated filenames prevent conflicts
- EXIF data preserved for JPEG images

## Security Features

- File type validation (MIME check)
- File size limitation (5MB)
- Unique filename generation
- No executable files allowed

## Database / Configuration

- No database required (file-based storage)
- Images stored directly on server
- No admin panel needed for this phase
- Manual file deletion via FTP if needed

## Future Enhancements

- Admin panel for image deletion
- Image categorization/tagging
- Bulk upload support
- Image editing tools
- Download original image
- Social media sharing
- Image statistics/analytics

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "File size exceeds 5MB" | Compress image or use smaller file |
| "Only JPG, PNG, GIF, WebP allowed" | Convert image to supported format |
| "Upload error" | Check file permissions, server PHP config |
| Images not displaying | Verify folder permissions (755) |
| Drag-drop not working | Use file input instead (click to select) |

## File Permissions

- Upload folder should be writable: `755` or `777`
- PHP must have permission to write files
- Windows: Usually no issues, but check folder properties

## Contact

For issues or enhancements:
- Email: `info@nyakabingoprimary.ug`
- Phone: School office

---

**Last Updated**: March 29, 2026
**Version**: 1.0
**Status**: Production Ready ✓

