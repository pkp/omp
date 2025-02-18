# CSV Import Export Plugin

## Table of Contents
- [Overview](#overview)
- [Usage Instructions](#usage-instructions)
- [CSV File Structure and Field Descriptions](#csv-file-structure-and-field-descriptions)
  - [Required Fields and Headers](#required-fields-and-headers)
- [Authors Data Organization](#authors-data-organization)
- [Examples](#examples)
- [Common Use Cases](#common-use-cases)
- [Best Practices and Troubleshooting](#best-practices-and-troubleshooting)
- [Limitations and Special Considerations](#limitations-and-special-considerations)

## Overview
The CSV Import Export Plugin is a command-line tool for importing submission data from a CSV file into OMP. It allows you to batch-import submissions using a properly formatted CSV file.

## Usage Instructions
### How to Run
Use the following command in your terminal:
```
php tools/importExport.php CSVImportExportPlugin [path_to_csv_file] [username]
```
- **[path_to_csv_file]**: The path to the CSV file containing submission data.
- **[username]**: The username to assign the imported submissions.

**Example:**
```
php tools/importExport.php CSVImportExportPlugin /home/user/submissions.csv johndoe
```

### Command Parameters Table

| Parameter         | Description                                             | Example                        |
|-------------------|---------------------------------------------------------|--------------------------------|
| [path_to_csv_file]| Path to the CSV file containing submission data       | /home/user/submissions.csv     |
| [username]        | Username to assign the imported submissions           | johndoe                        |

## CSV File Structure and Field Descriptions

The CSV file should have the following structure and fields:

| Column Name             | Description                                                  | Required | Example Value                                  |
|-------------------------|--------------------------------------------------------------|:--------:|------------------------------------------------|
| pressPath               | Identifier for the press                                     | Yes      | leo                                            |
| authorString            | Authors list; separate multiple authors with semicolons      | Yes      | "Given1,Family1,email@example.com;John,Doe,john@example.com" |
| title                   | Title of the submission                                      | Yes      | Title text                                     |
| abstract                | Summary or abstract of the submission                        | Yes      | Abstract text                                  |
| seriesPath              | Series identifier (optional if not applicable)               | No       | (leave empty if not applicable)                |
| year                    | Year of the submission                                       | No      | 2024 (leave empty if not applicable)                                           |
| isEditedVolume          | Flag indicating if it's an edited volume (1 = Yes, 0 = No)     | Yes      | 1 (leave empty if not applicable)                                               |
| locale                  | Locale code (e.g., en)                                         | Yes      | en                                             |
| filename                | Name of the file with submission content                     | Yes      | submission.pdf                                 |
| doi                     | Digital Object Identifier (if applicable)                    | No       | 10.1111/hex.12487                              |
| keywords                | Keywords separated by semicolons                             | No       | keyword1;keyword2;keyword3                      |
| subjects                | Subjects separated by semicolons                             | No       | subject1;subject2                              |
| bookCoverImage          | Filename for the cover image                                 | No       | coverImage.png                                 |
| bookCoverImageAltText   | Alternative text for the cover image                         | No       | Alt text, with commas                          |
| categories              | Categories separated by semicolons                           | No      | Category 1;Category 2;Category 3 (leave empty if not applicable)                |
| genreName               | Genre of the submission                                      | No      | MANUSCRIPT (leave empty if not applicable)                                     |

**Note:** Ensure that fields with commas are properly quoted.

### Required Fields and Headers

The CSV must contain exactly the following headers in the specified order:

**Expected Headers:**
```
pressPath,authorString,title,abstract,seriesPath,year,isEditedVolume,locale,filename,doi,keywords,subjects,bookCoverImage,bookCoverImageAltText,categories,genreName
```

**Required Headers (mandatory):**
```
pressPath,authorString,title,abstract,locale,filename
```

**Warning:** The CSV header order must match exactly as provided in sample.csv. Any deviation, such as additional headers, missing headers, or reordering, will cause the CLI command to crash.

## Authors Data Organization

Author's information is processed via the AuthorsProcessor (see AuthorsProcessor.php). In the CSV, author details should be provided in the `authorString` field following these rules:
- Multiple authors must be separated by a semicolon (`;`).
- Each author entry must contain comma-separated values in the following order:
  - Given Name (required)
  - Family Name (required)
  - Email Address (optional; if omitted, the tool defaults to the provided contact email)

**Example:**
```
"Given1,Family1,email@example.com;John,Doe,"
```

**Note:** All assets referenced in the CSV (e.g., files specified in `filename` or `bookCoverImage`) must reside in the same directory as the CSV file.

## Examples

### Command Example
**Command:**
```
php tools/importExport.php CSVImportExportPlugin /home/user/submissions.csv johndoe
```

**Example Output:**
```
Submission: "Title text" successfully imported.
Submission: "Another Title" successfully imported.
...
All submissions imported. 2 successes, 0 failures.
```

### Sample CSV File Snippet
```
pressPath,authorString,title,abstract,seriesPath,year,isEditedVolume,locale,filename,doi,keywords,subjects,bookCoverImage,bookCoverImageAltText,categories,genreName
leo,"Given1,Family1,given1@example.com;John,Doe,john@example.com",Title text,Abstract text,,2024,1,en,submission.pdf,10.1111/hex.12487,keyword1;keyword2,subject1;subject2,coverImage.png,"Alt text, with commas",Category 1;Category 2,MANUSCRIPT
```

## Common Use Cases
- **Batch Importing Submissions:** Import multiple submissions at once using a CSV file.
- **Data Migration:** Transfer submission data from legacy systems to OMP.
- **Automated Imports:** Integrate the tool into scripts for periodic data imports.

## Best Practices and Troubleshooting
- **Verify CSV Structure:** Always check your CSV against the sample structure provided above and ensure it strictly adheres to the required header order.
- **Check for Required Fields:** Ensure all mandatory fields (e.g., pressPath, authorString, title, abstract, locale, filename) are provided.
- **Validate Authors Format:** Confirm that the `authorString` field follows the format: Given Name, Family Name, Email (with multiple authors separated by semicolons).

## Limitations and Special Considerations
- The tool is command-line only; no web interface is available.
- **Warning:** CSV header mismatches—such as extra headers, missing headers, or headers in an incorrect order—will cause the CLI command to crash. Ensure the CSV exactly matches the header format provided in sample.csv.
