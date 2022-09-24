# Song Migrationen

**Tipp:** Wenn du bisher noch nicht mit dem CT-CLI (ChurchTools CLI-Tool) gearbeitet hast, lohnt es sich vielleicht kurz
die wichtigsten Informationen und Abläufe auf der [README.md](../../README.md) Seite anzuschauen.

**Background aus unserer Gemeinde:** Wir nutzten die Song-Arrangements bei uns in der Gemeinde nicht um verschiedene "
spielarten" eines Songs zu hinterlegen, sondern eigentlich ausschließlich um die Chordsheets / Noten nach Tonarten
sortiert abzulegen. Das im Ablaufplan hinterlegte Arrangement gibt für alle Musiker Auskunft in welcher Tonart der
Lobpreisleiter das Lied spielen möchte.

**Migrationen und Testmodus:** Alle Migrationen werden standardmäßig in einem "Test-Modus" ausgeführt. In diesem Modus
werden keine Daten in ChurchTools geändert, die Migration wird sozusagen nur simuliert. Dadurch hast du die Möglichkeit
im Log zu überprüfen ob du mit allen Änderungen einverstanden bist. Um eine Migration tatsächlich durchzuführen musst du
die Option `--no-testmode` an den Befehl hängen.

## Migration: Song-Arrangement BPMs

Oft werden die BPM-Zahlen nur an einem Song-Arrangement eingetragen. Der Music-Director am Sonntag muss dann die
Arrangements durchsuchen, ob er die richtige BPM-Zahl findet. Oft genug ist an "selbstgebauten" Chordsheet (bspw. für
deutsche Übersetzungen) keine BPM angegeben.

Die Migration soll dafür sorgen die BPM auf alle andere Song-Arrangements zu übertragen. Aufzurufen über den Befehl:

```bash
php ct.phar migrate:song-arrangement-bpm
```

Oder eingegrenzt auf eine bestimmte Song-Category:

```bash
php ct.phar migrate:song-arrangement-bpm --song-categories=13
```

Eigentlich gibt es für jeden Song nur vier Möglichkeiten, wie die Migration ausgeht:

**1. An keinem Arrangement ist eine BPM hinterlegt:**

```md
 - FAILED: Could not find a BPM in any Arrangement of this song [CTApi\Models\Song (#10)]
```

**2. An einem Song sind Arrangements mit unterschiedlichen BPM hinterlegt:**

```md
 - FAILED: Arrangements of song contains different BPMs: 22, 71 [CTApi\Models\Song (#15)]
```

**3. An allen Arrangements ist bereits die richtige Zahl eingetragen**

```md
 - SKIPPED: Skipped-ArrangementIds (already migrated): 1171, 1519, 2095, 2098; [CTApi\Models\Song (#287)]
```

**4. Die fehlenden BPMs werden an den Arrangements nachgetragen**

```md
 - SUCCESS: Migrated-ArrangementIds (to 154-BPM): 169; Skipped-ArrangementIds (already migrated): 168,
   320; [CTApi\Models\Song (#130)]
```

## Migration: Song-Arrangement Name

Wie schon beschrieben, haben die einzelnen Arrangements im Großteil der Fälle nur die Aufgabe den Song nach Tonarten zu
unterteilen. Das spiegelt sich auch in der Benennung der Arrangements nieder. Teilweise legen Musiker nur ein neues
Arrangement mit einer zusätzlichen Tonart an ohne den Namen des Arrangements zu ändern. Diese Migration ändert den
Arrangement Name.

```bash
php ct.phar migrate:song-arrangement-names
```

Wenn ein Arrangement Name auf einer von diesen Strings passt, wird der Name migriert auf das Format: `In [KEY]`. Das verhindert, dass gewollte spezielle Arrangement-Namen bestehen bleiben (bspw.: "Numbersystem", "Text", ...).

```php
[
    "Standard-Arrangement",
    "Standard Arrangement",
    "Neues Arrangement",
    "In [KEY]-Dur",
    "In [KEY]m",
    "In [KEY]",
    "in [KEY]-Dur",
    "in [KEY]m",
    "in [KEY]",
    "[KEY]-Dur",
    "[KEY]",
    "[KEY]-Dur",
    "[KEY]m",
];
```

Für die Migration der Song-Arrangements gibt es nur 3 mögliche Szenarien:

**1. Tonart (Key) in Arrangement ist nicht gesetzt:**

```md
 - SKIPPED: Key of model is null. Migration of Arrangement-Name is impossible. [CTApi\Models\SongArrangement (#1030)]
```

**2. Arrangement-Name ist bereits migriert oder wird nicht migriert:**

...bereits migriert:
```md
 - SKIPPED: No update required for arrangement with key: A and name: 'In A' [CTApi\Models\SongArrangement (#1000)]
```

...keine Migration, da Arrangement-Name bereits "manuell" gesetzt ist:
```md
 - SKIPPED: No update required for arrangement with key: A and name: 'Urban Live Version' [CTApi\Models\SongArrangement (#2479)]
```

**3. Migration wird durchgeführt:**

```md
 - SUCCESS: Successfully updated arrangement-name (key: E) from 'Neues Arrangement' to new name 'In E' [CTApi\Models\SongArrangement (#46)]
```

## Migration: Song ShouldPractice / "Zum Lernen markiert" löschen

In ChurchTools gibt es das Flag "Zum Lernen markieren" (engl.: should practice). Mit dieser Migration können alle Lieder "zurückgesetzt" werden und damit das "Zum Lernen markieren" Feld auf nein gesetzt werden:

```bash
php ct.phar migrate:song-should-practice-clear
```

Für diese simple Migration gibt es nur zwei Resultate:

**1. Feld steht auf "ja" und muss auf "nein" gesetzt werden:**

```md
 - SUCCESS: Updated song and set should-practice-flag to false. [CTApi\Models\Song (#35)]
```

**2. Feld steht bereits auf "nein":**

```md
 - SKIPPED: Should-practice-flag is already false. [CTApi\Models\Song (#990)]
```