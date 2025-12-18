<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Arial', 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333333;
        }
        
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body style="font-family: 'Arial', 'Inter', sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; color: #333333;">
    <!-- Main Container -->
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 900px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
        <!-- Header Section -->
        <tr>
            <td style="padding: 20px 40px; border-bottom: 2px solid #eeeeee;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <!-- Company Info -->
                        <td width="50%" valign="top" style="font-size: 11px; line-height: 1.4;">
                            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDQwNCIgaGVpZ2h0PSIyNTgzIiB2aWV3Qm94PSIwIDAgNDQwNCAyNTgzIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPg0KPHBhdGggZD0iTTE3NjAuMDEgOS4wNTI2N0MxNzA4LjcyIDM2Ljk2MzUgMTY5Mi4xMiA2NC44NzQ0IDE2MzEuNzcgMjE4Ljc2MUMxNTc5LjcyIDM1My43ODkgMTUzNy40OCA1MDYuMTY3IDE1MDIuNzggNjg2LjQ1NkMxNDg4LjQ1IDc2My40IDE0NzQuODcgODM1LjA2MyAxNDcyLjYgODQ2LjM3OEMxNDY4LjgzIDg2NC40ODIgMTQ2NC4zMSA4NjcuNSAxNDMzLjM4IDg3MS4yNzFDMTM3Ni4wNSA4NzguMDYgMTMzOS44NCA4OTAuMTMgMTMwNy40IDkxMi43NkMxMjU5Ljg4IDk0NS45NTIgMTE1OC44IDEwNDcuNzkgMTEyNy44NyAxMDkzLjhDMTAzMi4wNyAxMjM0Ljg3IDEwNDcuOTEgMTQ3MC4yMiAxMTU2LjUzIDE1MjYuOEMxMjIxLjQxIDE1NjAuNzQgMTMyOC41MiAxNTQxLjg5IDE0NDUuNDUgMTQ3Ni4yNkMxNDY3LjMyIDE0NjQuMTkgMTQ4Ni45NCAxNDU1Ljg5IDE0ODkuMiAxNDU4LjE1QzE0OTEuNDYgMTQ2MC40MiAxNTAzLjUzIDE1MDcuOTQgMTUxNC44NSAxNTY0LjUyQzE1NjYuMTQgMTgwOC45MiAxNjAzLjExIDE4NzAuMDMgMTcwMi42OCAxODcyLjI5QzE3OTkuOTkgMTg3NC41NSAxODYyLjYgMTc4Ny4wNSAxOTYwLjY3IDE1MTEuNzFMMjAwMS40IDEzOTcuMDVMMjAwOS43IDE0NDguMzVDMjAxNC45OCAxNDc3LjAxIDIwMjQuNzkgMTUxMC4yIDIwMzEuNTggMTUyMi4yN0MyMDU5LjQ5IDE1NjkuMDQgMjEyNC4zNiAxNTgwLjM2IDIyMDkuNiAxNTUzLjk2QzIzMDQuNjUgMTUyNC41NCAyMzk1LjE3IDE0NDMuODIgMjQ0Ni40NyAxMzQyLjc0QzI0NTcuNzggMTMxOS4zNSAyNDUyLjUgMTMyMy4xMyAyNDA0LjIyIDEzNjkuODlDMjI4Mi43NyAxNDg1LjMxIDIxNzcuOTIgMTUzMC41NyAyMTMyLjY2IDE0ODUuMzFDMjEwOS4yNyAxNDYxLjkzIDIxMTUuMzEgMTQzNS41MiAyMTc3LjkyIDEyNzcuODZDMjI0MC41MyAxMTIyLjQ3IDIyNTAuMzQgMTA3MS4xNyAyMjI3LjcxIDEwMDQuMDRDMjIxNC44OCA5NjQuMDU2IDIxOTkuOCA5NTAuNDc4IDIxNjguODcgOTUwLjQ3OEMyMTM4LjY5IDk1MC40NzggMjExMi4yOSA5OTEuMjEyIDIwODQuMzggMTA4Mi40OUMyMDcwLjggMTEyNi4yNCAyMDY3LjAzIDExMzIuMjggMjA2Mi41IDExMTcuOTRDMjA1OS40OSAxMTA4Ljg5IDIwNTQuMjEgMTEwMS4zNSAyMDUwLjQzIDExMDEuMzVDMjA0Ni42NiAxMTAxLjM1IDIwMzUuMzUgMTEwOC44OSAyMDI0LjAzIDExMTcuOTRDMjAwOC45NSAxMTMxLjUyIDE5OTkuMTQgMTE1Ni40MSAxOTc4Ljc3IDEyMzQuMTFDMTkzMC40OSAxNDE4LjkzIDE4ODQuNDggMTU1Ny43MyAxODQ2Ljc2IDE2MzMuMTZDMTgwOS44IDE3MDYuMzMgMTc2OS4wNiAxNzY1LjE3IDE3NjAuNzcgMTc1Ni44N0MxNzUzLjk4IDE3NTAuMDkgMTcxMS43MyAxNTMwLjU3IDE2OTkuNjYgMTQzNC43N0MxNjkyLjg3IDEzODMuNDcgMTY4NC41OCAxMjkxLjQ0IDE2ODEuNTYgMTIzMS4wOUMxNjc0LjAyIDEwOTkuODQgMTY3OC41NCAxMDc2LjQ1IDE3MzMuNjEgOTU2LjUxM0MxNzc5LjYyIDg1Ni45MzkgMTgxNi41OSA3NDAuNzY5IDE4NTAuNTMgNTg4LjM5MUMxODg3LjUgNDI0LjY5OCAxODk3LjMgMzA5LjI4MyAxODg1Ljk5IDE2My42OTRDMTg4MS40NiA5OS41NzQzIDE4NzUuNDMgNDIuMjQzOSAxODczLjE2IDM1LjQ1NDhDMTg2Ny44OCAyMS44NzY2IDE4MTcuMzQgMC4wMDA1MDk2NzMgMTc5MS42OSAwLjAwMDUwOTY3M0MxNzgyLjY0IDAuMDAwNTA5NjczIDE3NjguMzEgNC41MjY1OSAxNzYwLjAxIDkuMDUyNjdaTTE4MjEuODcgMTA1LjYwOUMxODY3LjEzIDI5Ny45NjggMTg2My4zNiA0ODAuNTIgMTgxMi4wNiA2NjAuMDU0QzE3OTMuOTYgNzIyLjY2NSAxNzEwLjIyIDk1NS4wMDQgMTY5Ni42NSA5NzkuMTQzQzE2NjcuMjMgMTAzMS4xOSAxNjk0LjM4IDQ1Mi42MDkgMTczMC41OSAyNDguOTM1QzE3NDEuMTUgMTg3LjA3OSAxNzY2LjA1IDEwMS44MzcgMTc4MC4zOCA3My4xNzIyQzE3OTMuMiA0Ny41MjQ0IDE4MTIuMDYgNjEuODU3IDE4MjEuODcgMTA1LjYwOVpNMTQ3OS4zOSAxMjkxLjQ0QzE0ODIuNDEgMTMwMC40OSAxNDY4LjA4IDEzMTQuODMgMTQzMi42MiAxMzQxLjIzQzEzNjkuMjYgMTM4OCAxMzU0LjkzIDEzOTUuNTQgMTMyNy43NyAxMzk1LjU0QzEyODAuMjUgMTM5NS41NCAxMjQ3LjA1IDEzMjMuMTMgMTI1Mi4zNCAxMjMxLjA5QzEyNTcuNjIgMTEzMS41MiAxMzQzLjYxIDk5NS43MzkgMTQyNS44NCA5NTUuMDA0TDE0NjUuODIgOTM0LjYzNkwxNDcxLjEgMTEwNi42M0MxNDc0LjExIDEyMDAuOTIgMTQ3Ny44OSAxMjgzLjkgMTQ3OS4zOSAxMjkxLjQ0Wk0yMDYzLjI2IDEyMDEuNjhDMjA2MSAxMjI3LjMyIDIwNDkuNjggMTI3NC4wOSAyMDM4LjM2IDEzMDUuMDJDMTk5Ni44OCAxNDE5LjY4IDE5OTYuMTIgMTM3OC4xOSAyMDM2Ljg2IDEyMzQuODdDMjA0OS42OCAxMTkwLjM2IDIwNjEuNzUgMTE1NC4xNSAyMDY0LjAxIDExNTQuMTVDMjA2Ni4yOCAxMTU0LjE1IDIwNjUuNTIgMTE3NS4yNyAyMDYzLjI2IDEyMDEuNjhaIiBmaWxsPSIjREEwRjdBIi8+DQo8cGF0aCBkPSJNODE0LjgxNCA3MTcuMzgyQzc5NS45NTUgNzI3LjE4OSA3NzcuMDk2IDc0My43ODQgNzcwLjMwNyA3NTcuMzYzQzc1NS4yMiA3ODUuMjc0IDc1My43MTIgODQ4LjYzOSA3NjYuNTM2IDg3Mi43NzhDNzg4LjQxMiA5MTIuMDA0IDg1NC43OTQgODgwLjMyMSA4ODIuNzA1IDgxNi45NTZDODk3LjAzOCA3ODQuNTE5IDkwMi4zMTggNzI1LjY4IDg5My4yNjYgNzA5LjgzOUM4ODQuOTY4IDY5Ny4wMTUgODUwLjI2OCA3MDAuMDMyIDgxNC44MTQgNzE3LjM4MloiIGZpbGw9IiNEQTBGN0EiLz4NCjxwYXRoIGQ9Ik0yMjE3LjkgNzE2LjYzQzIxNjMuNTkgNzQxLjUyMyAyMTQwLjk2IDgxOS4yMjEgMjE3MS44OSA4NzUuNzk3QzIxODIuNDUgODk2LjE2NCAyMjE3LjkgODkzLjkwMSAyMjQ1LjA2IDg3MS4yNzFDMjI4MC41MSA4NDEuODUxIDIzMDAuODggNzkyLjgxOSAyMjk3Ljg2IDc0NS4yOTVDMjI5NS42IDcwNi44MjMgMjI5NC44NSA3MDUuMzE0IDIyNzIuOTcgNzAzLjA1MUMyMjYwLjkgNzAyLjI5NyAyMjM2LjAxIDcwOC4zMzIgMjIxNy45IDcxNi42M1oiIGZpbGw9IiNEQTBGN0EiLz4NCjxwYXRoIGQ9Ik01MTMuODMgODI1LjI1NUM0OTUuNzI2IDg0MS4wOTYgNDk0LjIxNyA4NDQuODY4IDUwMi41MTUgODYyLjk3MkM1MDcuMDQxIDg3My41MzMgNTE2LjA5MyA4ODIuNTg1IDUyMS4zNzMgODgyLjU4NUM1MjcuNDA4IDg4Mi41ODUgNTM3LjIxNSA4ODguNjIgNTQ0LjAwNCA4OTYuMTYzQzU1NS4zMTkgOTA4Ljk4NyA1NTUuMzE5IDkxNS4wMjIgNTM4LjcyMyA5NzcuNjMzQzQ4Ny40MjggMTE2Ny43MyA0MDIuOTQxIDEzMDIgMjc2LjIxMSAxMzk0LjAzQzI2My4zODcgMTQwMy4wOCAyNjEuMTI0IDE0MDAuODIgMjQ3LjU0NSAxMzcwLjY1QzIzOS4yNDggMTM1MS43OSAyMjAuMzg5IDEyNjcuMyAyMDYuMDU2IDExODIuODJDMTcwLjYwMiA5NzMuMTA3IDE0OC43MjYgOTA4Ljk4NyAxMDIuNzExIDg4My4zMzlDODQuNjA2NCA4NzIuNzc5IDgwLjA4MDMgODcyLjc3OSA1Ni42OTU1IDg4NC4wOTRDMzQuMDY1MSA4OTQuNjU1IDI4Ljc4NDcgOTAyLjE5OCAyMS45OTU1IDkzNi4xNDRDLTE1LjcyMTggMTExMi42NiAtMy42NTIyNSAxMzU2LjMyIDQ4LjM5NzcgMTQ3MC45OEM3Mi41MzY4IDE1MjMuNzggOTguMTg0NiAxNTUxLjY5IDE0NS43MDggMTU3My41N0MyNDQuNTI4IDE2MjAuMzQgMzAyLjYxMyAxNTg0Ljg4IDQwOC4yMjEgMTQxMy42NUM0ODUuOTE5IDEyODguNDIgNDkxLjk1NCAxMjc1LjYgNTQxLjc0MSAxMTI4LjVDNjA4LjEyMyA5MzQuNjM1IDYxNC45MTIgODczLjUzMyA1NzguNzA0IDgyNi43NjNDNTU5Ljg0NSA4MDIuNjI0IDU0MC45ODYgODAxLjg3IDUxMy44MyA4MjUuMjU1WiIgZmlsbD0iI0RBMEY3QSIvPg0KPHBhdGggZD0iTTI5MTYuNDIgOTM4LjQwN0MyODU5LjA5IDk2MS43OTIgMjgxNi4xIDk5NC45ODMgMjc4MC42NCAxMDQyLjUxQzI3NDguMiAxMDg2LjI2IDI3MTQuMjYgMTEyMy45OCAyNzA3LjQ3IDExMjMuOThDMjcwNS4yMSAxMTIzLjk4IDI3MDQuNDUgMTEwMS4zNSAyNzA1LjIxIDEwNzQuMTlDMjcxMC40OSA5ODIuOTE0IDI2OTEuNjMgOTQyLjkzNCAyNjQzLjM1IDk0Mi45MzRDMjU5Mi4wNSA5NDIuOTM0IDI1NzIuNDQgOTg1LjE3NyAyNTE0LjM2IDEyMTYuNzZDMjQ3My42MiAxMzc1LjkzIDI0NzIuMTEgMTM4OCAyNDc0LjM4IDE0NjQuOTRDMjQ3Ni42NCAxNTU4LjQ4IDI0ODUuNjkgMTU3NS4wOCAyNTM5LjI1IDE1ODEuODdDMjYxMC4xNiAxNTkxLjY3IDI2MzYuNTYgMTU1OC40OCAyNjU2LjkzIDE0MzguNTRDMjY2Mi45NiAxMzk4LjU2IDI2NzMuNTIgMTM1My4zIDI2NzkuNTYgMTMzOC45N0MyNjk5Ljc3IDEyOTIuOTUgMjgxNi4xIDExMjEuNzEgMjg1Ni4wOCAxMDgwLjIyQzI4OTEuNTMgMTA0NC4wMiAyOTU5LjQyIDEwMDMuMjggMjk4NS4wNyAxMDAzLjI4QzI5OTkuNCAxMDAzLjI4IDI5OTIuNjEgMTA0My4yNiAyOTUxLjEyIDExOTIuNjJDMjg5NC41NSAxNDAxLjU4IDI4ODQuNzQgMTQ1NS4xNCAyODgzLjk5IDE1NjkuMDRDMjg4My45OSAxNjYzLjMzIDI4ODUuNSAxNjczLjkgMjkwMy42IDE3MTMuMTJDMjk0OC4xMSAxODA2LjY2IDMwNTUuOTggMTgzOC4zNCAzMTU3LjgyIDE3ODcuMDVDMzIxOS42NyAxNzU2Ljg3IDMyNjIuNjcgMTcwNy4wOSAzMzEyLjQ2IDE2MDguMjdDMzM3Mi44IDE0OTEuMzQgMzM5MS42NiAxNDAzLjA5IDMzNjcuNTIgMTM1OC41OEMzMzU2Ljk2IDEzMzguOTcgMzM1Ni45NiAxMzM4Ljk3IDMyMTEuMzcgMTYwMi45OUMzMTYwLjgzIDE2OTMuNTEgMzEwNC4yNiAxNzI0LjQ0IDMwODMuMTMgMTY3Mi4zOUMzMDY2LjU0IDE2MzMuOTIgMzA3MS44MiAxNTY4LjI5IDMxMDUuMDEgMTQxMi4xNEMzMTQ0LjI0IDEyMzAuMzQgMzE1NC44IDExNDguODcgMzE0OC43NiAxMDg2LjI2QzMxNDEuOTcgMTAxNS4zNSAzMTMyLjkyIDk4Ny40NCAzMTAxLjk5IDk1MS45ODZMMzA3NC44NCA5MjAuMzAzSDMwMTcuNTFDMjk3NC41MSA5MjAuMzAzIDI5NDguMTEgOTI0LjgyOSAyOTE2LjQyIDkzOC40MDdaIiBmaWxsPSIjREEwRjdBIi8+DQo8cGF0aCBkPSJNMzc4Ni45NCA5MzkuMTYxQzM3NjQuMzEgOTQ0LjQ0MSAzNzIyLjA3IDk2MS4wMzcgMzY5Mi42NSA5NzYuMTI0QzM1MjYuNjkgMTA2MC42MSAzNDQ4LjI0IDExNjkuMjQgMzQzOS45NCAxMzI0LjYzQzM0MzUuNDIgMTQxNS4xNSAzNDQ5Ljc1IDE0NjAuNDEgMzQ5OC4wMyAxNTA4LjY5QzM1NzQuOTcgMTU4NC44OCAzNjczLjc5IDE1ODcuOSAzODA1LjA1IDE1MTYuOTlDMzgzNi43MyAxNDk5LjY0IDM4NjMuODkgMTQ4Ni44MiAzODY1LjM5IDE0ODguMzNDMzg2Ni45IDE0ODkuODMgMzg1OS4zNiAxNTQxLjEzIDM4NDguOCAxNjAyLjIzQzM4MjYuOTIgMTczMi43MyAzODQ1LjAzIDE3MDIuNTYgMzY0Mi4xMSAxOTQ2LjIxQzM0NzIuMzggMjE1MC42NCAzNDI5LjM4IDIyMTcuMDIgMzQwMi45OCAyMzE4LjExQzMzOTAuMTYgMjM2Ny4xNCAzMzg3Ljg5IDI0ODAuMjkgMzM5OC40NSAyNTI2LjMxQzM0MDkuMDEgMjU2OC41NSAzNDQ3LjQ5IDI1ODMuNjQgMzU0MC4yNyAyNTgyLjg4QzM2MzYuODMgMjU4Mi44OCAzNjg1LjExIDI1NjQuNzggMzc0Ni45NiAyNTA4LjJDMzgyMC4xMyAyNDQwLjMxIDM4NTMuMzIgMjM1Mi4wNSAzODk0LjgxIDIxMTUuMTlDMzkwNS4zNyAyMDUzLjMzIDM5MjkuNTEgMTk0My45NSAzOTQ4LjM3IDE4NzEuNTNDMzk2Ny4yMyAxNzk5LjEyIDM5ODcuNiAxNzA1LjU4IDM5OTMuNjMgMTY2NC4wOUw0MDA0Ljk1IDE1ODcuOUw0MDg5LjQ0IDE1MDIuNjZDNDE4OS4wMSAxNDAxLjU4IDQyNDkuMzYgMTM1Ni4zMSA0MzIzLjI4IDEzMjYuMTRDNDM4OC45MSAxMjk5Ljc0IDQ0MDQgMTI4Ni45MSA0NDA0IDEyNTcuNVYxMjM0Ljg3TDQzNjMuMjYgMTIzOS4zOUM0MzQxLjM5IDEyNDEuNjUgNDMwOS43IDEyNDkuOTUgNDI5My4xMSAxMjU4LjI1QzQyNTguNDEgMTI3Ni4zNSA0MTQzLjc1IDEzNzAuNjUgNDA5NC43MiAxNDIxLjk0QzQwNzUuODYgMTQ0MC44IDQwNTAuOTYgMTQ2MS45MiA0MDM4Ljg5IDE0NjguNzFMNDAxNy4wMiAxNDgwLjAzTDQwMjIuMyAxNDU0LjM4QzQwNDUuNjggMTMyNS4zOSA0MDUxLjcyIDExNzAuNzUgNDAzNS44OCAxMTAwLjU5QzQwMjguMzMgMTA2OC4xNSA0MDE4LjUzIDEwNDkuMyAzOTk0LjM5IDEwMjMuNjVDMzk3Ny4wNCAxMDA0Ljc5IDM5NTUuMTYgOTc5LjE0MSAzOTQ1LjM2IDk2Ni4zMThDMzkxNy40NCA5MjkuMzU1IDM4NjQuNjQgOTIwLjMwMiAzNzg2Ljk0IDkzOS4xNjFaTTM5MDAuMDkgMTAyMi44OUMzOTExLjQxIDEwMjguOTMgMzkyMi43MiAxMDM3LjIzIDM5MjQuOTkgMTA0MUMzOTMxLjAyIDEwNTAuOCAzODM0LjQ3IDEyNjMuNTMgMzc5MC43MSAxMzM3LjQ2QzM3NTYuNzcgMTM5NS41NCAzNzM0Ljg5IDE0MTUuOTEgMzY5Ny45MyAxNDIyLjdDMzU4MS43NiAxNDQ0LjU3IDM2MjIuNDkgMTE0OS42MiAzNzQ5LjIyIDEwNDYuMjhDMzgwMC41MiAxMDA0Ljc5IDM4NDkuNTUgOTk3LjI0NiAzOTAwLjA5IDEwMjIuODlaTTM3NzQuMTIgMTkwMi40NkMzNzY4Ljg0IDE5MjQuMzQgMzc0OC40NyAyMDA4LjgzIDM3MjkuNjEgMjA4OS41NEMzNjc5LjA3IDIzMDQuNTMgMzY2My4yMyAyMzQ2LjAyIDM2MDUuMTQgMjQxOS4xOUMzNTc2LjQ4IDI0NTYuMTUgMzUxOS4xNSAyNDkyLjM2IDM0NzkuOTIgMjQ5OC40QzM0NTkuNTYgMjUwMS40MSAzNDQ4Ljk5IDI0NDEuMDcgMzQ2MS4wNiAyMzc5Ljk2QzM0NzAuODcgMjMyNy4xNiAzNTMzLjQ4IDIxOTguOTIgMzU5NC41OCAyMTA0LjYzQzM2NDUuMTIgMjAyNi45MyAzNzY5LjU5IDE4NjMuMjQgMzc3Ny44OSAxODYzLjI0QzM3ODAuOTEgMTg2My4yNCAzNzc5LjQgMTg4MS4zNCAzNzc0LjEyIDE5MDIuNDZaIiBmaWxsPSIjREEwRjdBIi8+DQo8cGF0aCBkPSJNNzMxLjA4IDk2OS4zMzRDNzAwLjE1MiAxMDA5LjMxIDYyMi40NTQgMTI1NC40OCA2MDguODc2IDEzNTQuODFDNTk5LjgyNCAxNDIxLjk0IDYwOC4xMjIgMTQ4OS44MyA2MjkuMjQzIDE1MjEuNTJDNjYwLjE3MiAxNTY5LjA0IDcyMS4yNzQgMTU4MC4zNiA4MDYuNTE1IDE1NTMuOTVDODk1LjUyOCAxNTI2LjggOTgzLjc4NiAxNDUyLjEyIDEwMzEuMzEgMTM2My44NkwxMDU2Ljk2IDEzMTYuMzNMMTAwMS4xNCAxMzY5Ljg5QzkwOS4xMDYgMTQ1Ny40IDgzNS45MzQgMTUwMC4zOSA3NzcuODUgMTUwMS4xNUM3MzMuMzQzIDE1MDEuMTUgNzEyLjIyMSAxNDgwLjAzIDcxNy41MDIgMTQ0MC44QzcxOS43NjUgMTQyNC45NiA3NDUuNDEzIDEzNTEuNzkgNzc1LjU4NyAxMjc3Ljg2QzgxOC41ODQgMTE3My4wMSA4MzEuNDA4IDExMzIuMjcgODMzLjY3MSAxMDkzLjhDODM4LjE5NyAxMDM3Ljk4IDgyNi44ODIgOTg2LjY4NCA4MDUuMDA2IDk2NC44MDhDNzg0LjYzOSA5NDQuNDQxIDc0OS45MzkgOTQ1Ljk1IDczMS4wOCA5NjkuMzM0WiIgZmlsbD0iI0RBMEY3QSIvPg0KPC9zdmc+DQo=" alt="Viding" style="height: 50px; margin-bottom: 10px;" />
                            <p style="margin: 0 0 4px 0;">PT. Aku Bisa Ibadah</p>
                            <p style="margin: 0 0 4px 0;">+62 878-4283-1668</p>
                            <p style="margin: 0 0 4px 0;">halo@viding.co</p>
                            <p style="margin: 0;">No. NPWP: 92.248.785.5-086.000</p>
                        </td>
                        
                        <!-- Quotation Info -->
                        <td width="50%" valign="top" style="text-align: right;">
                            <div style="font-size: 18px; font-weight: 700; color: #FF1493; margin-bottom: 10px;">QUOTATION {{ $quotation->quotation_number }}</div>
                            <div style="font-size: 12px; line-height: 1.4;">
                                <p style="margin: 0 0 4px 0;">{{ $quotation->quotation_date->format('d/m/Y') }}</p>
                                <p style="margin: 0 0 4px 0; font-weight: bold;">{{ $quotation->customer->name }}</p>
                                <p style="margin: 0 0 4px 0;">{{ $quotation->customer->phone }}</p>
                                <p style="margin: 0 0 4px 0;">{{ $quotation->customer->email }}</p>
                                <p style="margin: 0;font-weight: bold;">Valid until: {{ $quotation->valid_until->format('d/m/Y') }}</p>
                                <p style="margin: 0;font-weight: bold;font-size: 22px;">Rp. {{ number_format($quotation->total, 0, ',', '.') }}</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <!-- Informasi Section Title -->
        <tr>
            <td style="padding: 20px 40px 10px 40px;">
                <div style="font-size: 18px; font-weight: 600; color: #333333;">Informasi</div>
            </td>
        </tr>
        
        <!-- Items Table -->
        <tr>
            <td style="padding: 0 40px 30px 40px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #FF1493; color: #ffffff;">
                            <th style="padding: 12px 15px; text-align: left; font-weight: 600; font-size: 12px;">Item</th>
                            <th style="padding: 12px 15px; text-align: left; font-weight: 600; font-size: 12px;">Unit Price</th>
                            <th style="padding: 12px 15px; text-align: center; font-weight: 600; font-size: 12px;">Quantity</th>
                            <th style="padding: 12px 15px; text-align: right; font-weight: 600; font-size: 12px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation->items as $item)
                        <tr style="border-bottom: 1px solid #eeeeee;">
                            <td style="padding: 12px 15px; font-size: 12px;">{{ $item->description }}</td>
                            <td style="padding: 12px 15px; font-size: 12px;">{{ format_currency($item->unit_price) }}</td>
                            <td style="padding: 12px 15px; font-size: 12px; text-align: center;">{{ $item->quantity }}</td>
                            <td style="padding: 12px 15px; font-size: 12px; text-align: right;">{{ format_currency($item->total) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        
        <!-- Summary Section -->
        <tr>
            <td style="padding: 0 40px 30px 40px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <!-- Bank Info -->
                        <td width="50%" valign="top">
                            <div style="font-weight: 600; color: #FF1493; margin-bottom: 8px; font-size: 14px;">Transfer Account</div>
                            <div style="font-weight: 600; color: #333333; font-size: 12px; margin-bottom: 4px;">BCA 7030885656 an PT. Aku Bisa Ibadah</div>
                            <div style="font-weight: 600; color: #333333; font-size: 12px;">Mandiri 115-0000-8989-00 an PT. Aku Bisa Ibadah</div>
                        </td>
                        
                        <!-- Totals -->
                        <td width="50%" valign="top">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size: 12px; padding-bottom: 8px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-weight: 600; color: #666666;">Total Tagihan</td>
                                                <td style="text-align: right; color: #333333;">Rp. {{ number_format($quotation->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 12px; padding-bottom: 8px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-weight: 600; color: #666666;">Tax {{ $quotation->tax_percentage }}%</td>
                                                <td style="text-align: right; color: #333333;">Rp. {{ number_format($quotation->tax_amount, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 12px; padding-bottom: 8px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-weight: 600; color: #666666;">Discount</td>
                                                <td style="text-align: right; color: #333333;">Rp. {{ number_format($quotation->discount, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px; font-weight: 700; padding-top: 15px; border-top: 1px solid #dddddd;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-weight: 700; color: #333333;">Total Pembayaran</td>
                                                <td style="text-align: right; font-weight: 700; color: #333333;">{{ format_currency($quotation->total) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <!-- Notes Section -->
        <tr>
            <td style="padding: 0 40px 30px 40px; font-size: 11px; line-height: 1.4;">
                <div style="font-weight: 600; margin-bottom: 10px; font-size: 12px;">Notes:</div>
                <p style="margin: 0 0 15px 0;">{{ $quotation->notes ?? 'No additional notes.' }}</p>
                
                <div style="margin-top: 15px;">
                    <p style="margin: 2px 0;">1. Pesanan yang sudah dipesan tidak bisa dibatalkan, hanya bisa di reschedule atau diganti klien.</p>
                    <p style="margin: 2px 0;">2. Mempelai wajib menyiapkan data yang dibutuhkan maksimal H-14 event.</p>
                    <p style="margin: 2px 0;">3. Harap konfirmasi bukti pembayaran setelah melakukan pembayaran.</p>
                    <p style="margin: 2px 0;">4. Tidak ada biaya tambahan untuk perubahan tanggal selama layanan Viding masih tersedia di tanggal yang baru.</p>
                    <p style="margin: 2px 0;">5. Dalam kondisi force majeur (bencana alam, kerusuhan dan sebagainya) klien tidak bisa menuntut secara perdata dan pidana atas batalnya pelaksanaan layanan Viding.</p>
                </div>
            </td>
        </tr>
        
        <!-- Footer -->
        <tr>
            <td style="background-color: #FF1493; color: #ffffff; padding: 15px 40px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-weight: 600; font-size: 11px;">© 2025 Viding - All rights reserved.</td>
                        <td style="text-align: right; font-size: 11px;">{{ $quotation->created_at->format('d-m-Y H:i:s') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>