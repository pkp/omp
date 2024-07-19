# CSV Import Plugin for OMP
This application will convert a CSV file into a list of OMP publications/submissions. If the user has book cover images, as well as submission PDFs, these files should be stored in the `coverImages` and `submissionPdfs`, respectively, for your correct usage.

> Note: This is NOT a comprehensive CSV converter, and many fields are left out.

## 1. How to Use
From the CLI command, you should use this way, starting on the OMP root directory:

```
php tools/importExport.php CSVImportExportPlugin <BASE_PATH>/<CSV_FILE_NAME>.csv <USERNAME>
```

, where `<BASE_PATH>` is where the CSV file is located, `<CSV_FILE_NAME>` is the name of the CSV file you want to add the books and `<USERNAME>` is a valid OMP username registered.

> Note: The `<BASE_PATH>` will be used also to get the assets (the Book PDF and the cover image). It'll be explained later.

## 2. About the CSV file

### 2.1. Description
The CSV must be in this format:

`pressPath,authorString,title,abstract,seriesPath,year,isEditedVolume,locale,filename,doi,keywords,subjects,bookCoverImage,bookCoverImageAltText,categories`

1. **pressPath**: **(required)** is the path for the press the user wants to insert a publication. If this field is not present, the tool will jump for the other line of CSV file.
2. **authorString**: is the list of authors presents on the submission. For each author, it contains the Surname, the given name and the email address. For each author, the string format must be on the following format: `Author1 Surname1<author@pkp.sfu.ca>`. Each author must be separated by a semicolon (e.g. `Author1 Surname1<author@pkp.sfu.ca>;Author2 Surname2<author2@sfu.pkp.ca>`).
3. **title**: **(required)** the submission's title.
4. **abstract**: **(required)** the submission's abstract.
5. **seriesPath**: the path for the series this submission is included.
6. **year**: the submission's year.
7. **isEditedVolume**: sets the `work_type` for the submission.
8. **locale**: **(required)** the submission's locale. Must be one of the supported locales for this press. If it's not present, the tool will jump for the next CSV line and will not include this submission.
9. **filename**: **(required)** the PDF file name that must be present on the `submissionPdfs` directory for this submission.
10. **doi**: the submission's DOI link.
11. **keywords**: the submission's keywords. If the submission presents more than one keyword, they need to be separated by a semicolon (e.g. `keyword1;keyword2`);
12. **subjects**: the submission's subjects. If the submission presents more than one subject, they need to be separated by a semicolon (e.g. `subject1;subject2`);
13. **bookCoverImage**: the book cover image filename. This file must be on the directory `coverImages` and ought to be in one of these formats: *gif, jpg, png or webp*. If the image isn't in one of these formats, it won't be added to the submission.
14. **bookCoverImageAltText**: the alt text for the book cover image. It'll only work if the bookCoverImage is present.
15. **categories**: the submission's categories. All categories present here must be already added to the Press to work correctly. If the submission presents more than one category, they must be separated by a semicolon (e.g. `Category 1;Category 2`).

## 3. Instructions
1. Fill the CSV file correctly. You can use the `sample.csv` file as an example.
2. Place your CSV file in a place of your preference.
3. If there's any Book PDF or Cover Images, starting on the same directory where the CSV file is located, create the directory `submissionPdfs` and the directory `coverImages`.
4. Place the Submission PDFs on the `submissionPdfs` directory and the cover images on the `coverImages` directory, respectively.
	> Note: the Submission PDFs and the cover images must have the same name as in the CSV file.
5. Run the command present on the [How to Use](#1-how-to-use) section.
6. The commands should run correctly and add all the submissions present on it.
