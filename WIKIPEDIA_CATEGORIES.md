# Wikipedia Category Import Expansion

## Overview

This document describes the expanded Wikipedia category imports for StartupGraph, addressing GitHub issue #74.

## What Was Added

### New Category Types

1. **Extended Time Range**: Added technology and American companies from 2015-2019 (previously only covered 2020-2025)

2. **Geographic Expansion**: 
   - US startup hubs: San Francisco, Silicon Valley, Seattle, NYC, Boston, Austin, LA
   - International hubs: London, Berlin, Tel Aviv, Singapore, Toronto, Sydney
   - Country-specific categories: Canada, UK, Germany, Israel, Singapore, Australia

3. **Industry-Specific Categories**:
   - Software companies
   - Internet companies
   - Fintech companies
   - AI/ML companies
   - Robotics companies
   - Biotechnology companies
   - Cybersecurity companies
   - SaaS companies
   - E-commerce companies
   - Social networking companies
   - Gaming companies
   - Mobile app companies
   - Cloud computing companies
   - Data management companies

4. **Startup Ecosystem Categories**:
   - Y Combinator companies
   - Sequoia Capital funded companies
   - Andreessen Horowitz funded companies
   - Venture capital-backed companies

5. **Business Model Categories**:
   - Subscription software
   - Platform companies
   - Marketplace companies

## Enhanced Data Quality

### Metadata Extraction
The importer now extracts rich metadata from category names:
- **Year**: Founding year from category names
- **Country**: Maps locations to country codes (US, GB, DE, IL, SG, AU, CA)
- **City**: Specific city information for hub-based categories
- **Industry Category**: Business classification based on category type

### Data Validation
Added comprehensive validation to filter out:
- Disambiguation pages
- List pages and portals
- Generic terms without company context
- Purely numeric entries
- Pages with insufficient company indicators
- Overly short or long titles

### Deduplication
Enhanced the existing deduplication logic in BaseBulkImporter:
- Matches by website domain first
- Falls back to name matching
- Only updates null fields (preserves existing data)
- Generates unique slugs to prevent conflicts

## Categories Added (Total: ~70 categories)

### Time-based (16 categories)
- Technology companies 2015-2025 (11 years)
- American companies 2015-2025 (11 years)
- International companies 2020-2024 (5 years)

### Location-based (19 categories)
- 7 US startup hubs
- 6 international hubs  
- 6 country-specific categories

### Industry-based (14 categories)
- Software, Internet, Fintech, AI/ML, Robotics, Biotech
- Cybersecurity, SaaS, E-commerce, Social, Gaming
- Mobile, Cloud, Data management

### Ecosystem-based (4 categories)
- Y Combinator, Sequoia, A16z, VC-backed

### Business model (3 categories)
- Subscription, Platform, Marketplace

## Testing

Created comprehensive test suite in `test_wikipedia_import.php`:
- Validates metadata extraction for different category types
- Tests company validation logic
- Ensures proper filtering of invalid entries

## Usage

The enhanced importer maintains the same interface:

```php
$importer = new WikipediaCategoryImporter();
$result = $importer->start();
```

## Expected Impact

- **Quantity**: Expected to add 10,000+ new companies across expanded categories
- **Quality**: Better data quality through enhanced validation
- **Coverage**: More comprehensive startup ecosystem coverage
- **Geographic**: Global startup hub representation
- **Temporal**: Extended historical coverage back to 2015