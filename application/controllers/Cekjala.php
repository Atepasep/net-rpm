<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cekjala extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		if ($this->session->userdata('userrpm') != true) {
			$url = base_url('Auth');
			redirect($url);
		}

		$this->load->model('usermodel');
		$this->load->model('helpermodel');
		$this->load->model('cekjalamodel');
		$this->load->model('rpmmodel');
		$this->load->library('Pdf');
		include_once APPPATH . '/third_party/phpqrcode/qrlib.php';
	}

	public function index()
	{
		$header['header'] = [
			'menu' => 'cekjala'
		];
		$data = [
			'data' => $this->cekjalamodel->getdata(),
			'input' => $this->cekjalamodel->getdatainput()
		];
		$footer['footer'] = [
			'menu' => 'cekjala'
		];
		$this->load->view('layouts/header', $header);
		$this->load->view('cekjala', $data);
		$this->load->view('layouts/footer', $footer);
	}
	public function clear()
	{
		$this->session->set_userdata('tglcek', date('Y-m-d'));
		$this->session->unset_userdata('msncek');
		$this->session->set_userdata('nikcek', $this->session->userdata('nik'));
		$this->session->unset_userdata('shiftcek');
		$this->session->unset_userdata('caricek');
		$url = base_url() . 'cekjala';
		redirect($url);
	}
	public function getmesin()
	{
		$data['formname'] = 'cekjala';
		$this->load->view('getmesin', $data);
	}
	public function getpersonil($mode = 0)
	{
		$data = [
			'mode' => $mode,
			'formname' => 'cekjala'
		];
		$this->load->view('getpersonil', $data);
	}
	public function getpo()
	{
		$this->load->view('getpoinsnopot');
	}
	public function tambahdata()
	{
		$data = [
			'jamkerja' => $this->cekjalamodel->getjamkerja()
		];
		$this->load->view('addcekjala', $data);
	}
	public function editdata($id)
	{
		$data = [
			'jamkerja' => $this->cekjalamodel->getjamkerjatotal(),
			'data' => $this->cekjalamodel->getdatabyid($id),
			'mode' => 'edit'
		];
		$this->load->view('editcekjala', $data);
	}
	public function viewdata($id)
	{
		$data = [
			'jamkerja' => $this->cekjalamodel->getjamkerjatotal(),
			'data' => $this->cekjalamodel->getdatabyid($id),
			'mode' => 'view'
		];
		$this->load->view('editcekjala', $data);
	}
	public function editrpm($id, $idrpm = 0)
	{
		$header['header'] = [
			'menu' => 'rpm'
		];
		$data = [
			'idjam' => $id,
			'data' => $this->rpmmodel->getdatarpmbyid($id),
			'lastpo' => $idrpm == 0 ? $this->rpmmodel->getlastpo() : $this->rpmmodel->getdatarpmnya($idrpm),
			'baru' => $idrpm == 0 ? 'ya' : '',
			'rusak' => $this->rpmmodel->getkoderusak()
		];
		$footer['footer'] = [
			'menu' => 'rpm'
		];
		$this->load->view('layouts/header', $header);
		$this->load->view('editrpm', $data);
		$this->load->view('layouts/footer', $footer);
	}
	public function getdatamesin()
	{
		$kode = $_POST['isi'];
		$data = $this->rpmmodel->getdatamesin($kode);
		$html = '';
		if ($data->num_rows() > 0) {
			$no = 0;
			foreach ($data->result_array() as $dt) : $no++;
				$html .= '<tr>';
				$html .= '<td>#' . $no . '</td>';
				$html .= '<td class="font-kecil">' . $dt['mach_no'] . '</td>';
				$html .= '<td class="font-kecil">' . trim($dt['specifik']) . '</td>';
				$html .= '<td class="text-center">';
				$html .= '<a href="#" class="btn btn-success p-0 btn-flat font-kecil" id="pilihmesin" rel="' . $dt['id'] . '" rel2="' . $dt['mach_no'] . '" rel3="' . $dt['specifik'] . '">Pilih</a>';
				$html .= '</td>';
				$html .= '</tr>';
			endforeach;
		}
		$send = array('data' => $html, 'jml' => $data->num_rows());
		echo json_encode($send);
	}
	public function getdatapersonil()
	{
		$kode = $_POST['isi'];
		$data = $this->usermodel->getdatapersonil($kode);
		$html = '';
		if ($data->num_rows() > 0) {
			$no = 0;
			foreach ($data->result_array() as $dt) : $no++;
				$html .= '<tr>';
				$html .= '<td>#' . $no . '</td>';
				$html .= '<td class="font-kecil line-11"><span class="text-pink font-10">' . $dt['noinduk'] . '</span><br>' . trim($dt['nama']) . '</td>';
				$html .= '<td class="font-kecil">' . trim($dt['grup']) . '</td>';
				$html .= '<td class="text-center">';
				$html .= '<a href="#" class="btn btn-success p-0 btn-flat font-kecil" id="pilihpersonil" rel="' . $dt['id'] . '" rel2="' . $dt['noinduk'] . '" rel3="' . $dt['nama'] . '">Pilih</a>';
				$html .= '</td>';
				$html .= '</tr>';
			endforeach;
		}
		$send = array('data' => $html, 'jml' => $data->num_rows());
		echo json_encode($send);
	}
	public function getdatapoinsno()
	{
		$kode = $_POST['isi'];
		$data = $this->cekjalamodel->getdatapoinsno($kode);
		$html = '';
		if ($data->num_rows() > 0) {
			$no = 0;
			if ($data->num_rows() == 1) {
				$xdata = $data->row_array();
				$arry = [
					'rel' => $xdata['po'],
					'rel2' => $xdata['item'],
					'rel3' => $xdata['dis'],
					'rel4' => $xdata['insno']
				];
				$html = $arry;
			} else {
				foreach ($data->result_array() as $dt) : $no++;
					$html .= '<tr>';
					$html .= '<td>#' . $no . '</td>';
					$html .= '<td class="font-kecil">' . viewsku($dt['po'], $dt['item'], $dt['dis']) . '</td>';
					$html .= '<td class="font-kecil">' . trim($dt['insno']) . '</td>';
					$html .= '<td class="font-kecil text-teal">' . trim($dt['spek']) . '</td>';
					$html .= '<td class="text-center">';
					$html .= '<a href="#" class="btn btn-success p-0 btn-flat font-kecil" id="pilihpo" rel="' . $dt['po'] . '" rel2="' . $dt['item'] . '" rel3="' . $dt['dis'] . '" rel4="' . $dt['insno'] . '">Pilih</a>';
					$html .= '</td>';
					$html .= '</tr>';
				endforeach;
			}
		}
		$send = array('data' => $html, 'jml' => $data->num_rows());
		echo json_encode($send);
	}
	public function addsession()
	{
		$msn = isset($_POST['msn']) ? $_POST['msn'] : '';
		$shift = isset($_POST['shift']) ? $_POST['shift'] : '';
		$tgl = tglmysql($_POST['tgl']);
		$this->session->set_userdata('tglcek', $tgl);
		if ($msn != '') {
			$this->session->set_userdata('msncek', $msn);
		} else {
			$this->session->unset_userdata('msncek');
		}
		if ($shift != '') {
			$this->session->set_userdata('shiftcek', $shift);
		} else {
			$this->session->unset_userdata('shiftcek');
		}
		echo 1;
	}
	public function resetmsn()
	{
		$this->session->unset_userdata('msncek');
		$url = base_url() . 'cekjala';
		redirect($url);
	}
	public function resetpersonil($mode = 0)
	{
		$this->session->unset_userdata('nikcek');
		$url = base_url() . 'cekjala';
		redirect($url);
	}
	public function addpersonil()
	{
		$msn = isset($_POST['msn']) ? $_POST['msn'] : '';
		$mode = isset($_POST['mode']) ? $_POST['mode'] : 0;
		if ($msn != '') {
			$this->session->set_userdata('nikcek', $msn);
		} else {
			$this->session->unset_userdata('nikcek');
		}
		echo 1;
	}
	public function getnomorlot()
	{
		$data = [
			'trim(po)' => trim($_POST['po']),
			'trim(item)' => trim($_POST['item']),
			'dis' => $_POST['dis'],
			'trim(insno)' => trim($_POST['ins']),
		];
		$html = '';
		$pertama = 0;
		$data = $this->rpmmodel->getnomorlot($data);
		if (count($data) > 0) {
			for ($i = $data['lotakhir'] + 1; $i <= $data['jmllot']; $i++) {
				if ($pertama == 0) {
					$pertama = $i;
				}
				$html .= '<option value="' . $i . '">' . $i . '</option>';
			}
		}
		$send = array('data' => $html, 'lotnya' => $pertama);
		echo json_encode($send);
	}
	public function simpandata()
	{
		$data = $_POST;
		$data['id_waktu'] = $data['wkt'];
		$data['noinduk_cek'] = $this->session->userdata('nikcek');
		$data['kakesu'] = toAngka($data['kakesu']);
		$data['meai'] = toAngka($data['meai']);
		unset($data['wkt']);

		$hasil = $this->cekjalamodel->simpandata($data);
		echo $hasil;
	}
	public function hapusdata($id)
	{
		$data = $this->cekjalamodel->hapusdata($id);
		if ($data) {
			$url = base_url() . 'cekjala';
			redirect($url);
		}
	}
	public function getdetailinst()
	{
		$data = [
			'trim(tb_netinstr.po)' => trim($_POST['po']),
			'trim(tb_netinstr.item)' => trim($_POST['item']),
			'tb_netinstr.dis' => $_POST['dis'],
			'trim(tb_netinstr.insno)' => trim($_POST['insno']),
		];
		$hasil = $this->cekjalamodel->getdetailinst($data);
		echo json_encode($hasil->result());
	}
	public function getmedok()
	{
		$data = [
			'trim(tb_netinstr.po)' => trim($_POST['po']),
			'trim(tb_netinstr.item)' => trim($_POST['item']),
			'tb_netinstr.dis' => $_POST['dis'],
			'trim(tb_netinstr.insno)' => trim($_POST['insno']),
		];
		$hasil = $this->cekjalamodel->getmedok($data);
		echo json_encode(['data' => $hasil]);
	}

	public function caricek()
	{
		$kata = strtoupper($_POST['kata']);
		$this->session->set_userdata('caricek', $kata);
		echo 1;
	}
	public function clearrepcek()
	{
		$this->session->set_userdata('tglrepcek', $this->session->userdata('tglcek'));
		$this->session->set_userdata('msnrepcek', $this->session->userdata('msncek'));
		$url = base_url() . 'cekjala/repcek';
		redirect($url);
	}
	public function repcek()
	{
		$header['header'] = [
			'menu' => 'cekjala'
		];
		$data = [
			'data' => $this->cekjalamodel->cekjalarep(),
			'input' => $this->cekjalamodel->getdatainput(),
			'mesin' => $this->rpmmodel->getdatamesinall(),
		];
		$footer['footer'] = [
			'menu' => 'cekjala'
		];
		$this->load->view('layouts/header', $header);
		$this->load->view('repcekjala', $data);
		$this->load->view('layouts/footer', $footer);
	}
	public function repaddsession()
	{
		$this->session->set_userdata('tglrepcek', tglmysql($_POST['tgl']));
		$this->session->set_userdata('msnrepcek', $_POST['msn']);
		echo 1;
	}

	public function export_pdf()
	{
		$msn = $this->session->userdata('msnrepcek');
		$tgl = $this->session->userdata('tglrepcek');

		if (empty($msn) || empty($tgl)) {
			show_error('Tanggal dan mesin belum dipilih atau belum di-apply.');
			return;
		}

		$data = $this->cekjalamodel->cekjalarep()->result_array();

		$pdf = new PDF('L', 'mm', 'A4');
		$pdf->AliasNbPages();

		$pdf->AddFont('Lato', '', 'Lato-Regular.php');
		$pdf->AddFont('Latob', '', 'Lato-Bold.php');

		$pdf->SetMargins(12, 8, 12);
		$pdf->SetAutoPageBreak(true, 8);

		$toPdf = function ($text) {
			$text = strip_tags((string) $text);
			return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
		};

		$drawHeaderBox = function () use ($pdf, $toPdf, $msn, $tgl) {
			$x = 12;
			$y = 10;
			$boxH = 22;

			$pdf->Rect($x, $y, 45, $boxH);
			$pdf->Image(
				FCPATH . 'assets/img/logo2.png',
				$x + 7.5,
				$y + 2,
				26,
				18
			);

			$infoWidth = 55;
			$infoX = $x + 273 - $infoWidth;
			$infoY = $y;

			$pdf->Rect($infoX, $infoY, $infoWidth, $boxH);
			$pdf->Line($infoX, $infoY + 7.3, $infoX + $infoWidth, $infoY + 7.3);
			$pdf->Line($infoX, $infoY + 14.6, $infoX + $infoWidth, $infoY + 14.6);

			$pdf->SetFont('Lato', '', 9);

			$pdf->SetXY($infoX + 1, $infoY + 1.5);
			$pdf->Cell(15, 4, 'No. Dok.', 0, 0);
			$pdf->Cell(35, 4, ': FM-NT-07', 0, 1);

			$pdf->SetXY($infoX + 1, $infoY + 8.8);
			$pdf->Cell(15, 4, 'Revisi', 0, 0);
			$pdf->Cell(35, 4, ': 0', 0, 1);

			$pdf->SetXY($infoX + 1, $infoY + 16.1);
			$pdf->Cell(15, 4, 'Tanggal', 0, 0);
			$pdf->Cell(35, 4, ': 15-12-2011', 0, 1);

			$titleWidth = 273 - 45 - $infoWidth;
			$pdf->Rect($x + 45, $y, $titleWidth, $boxH);
			$pdf->SetFont('Latob', '', 14);
			$pdf->SetXY($x + 45, $y + 7);
			$pdf->Cell($titleWidth, 8, 'PENGECEKAN MEAI JALA', 0, 0, 'C');

			$subY = $y + $boxH;
			$pdf->Rect($x, $subY, 273, 6);
			$pdf->SetFont('Latob', '', 9);
			$pdf->SetXY($x + 2, $subY + 1);
			$pdf->Cell(135, 4, 'TANGGAL : ' . $toPdf($tgl), 0, 0, 'R');
			$pdf->Cell(135, 4, 'MESIN : ' . $toPdf($msn), 0, 1, 'L');

			$pdf->Ln(4);
		};


		$widths = [
			29,
			39,
			30,
			17,
			10,
			13,
			10,
			13,
			10,
			13,
			11,
			13,
			10,
			13,
			15,
			13,
			16
		];

		$drawHeader = function () use ($pdf, $widths) {
			$pdf->SetFont('Latob', '', 9);
			$pdf->SetFillColor(220, 220, 220);
			$pdf->SetTextColor(0, 0, 0);

			$x = $pdf->GetX();
			$y = $pdf->GetY();
			$hHeader = 10;


			$leftHeaders = [
				0 => 'Jam',
				1 => 'Nama',
				2 => 'SKU',
				3 => 'Sukioroshi'
			];
			foreach ($leftHeaders as $idx => $label) {
				$pdf->Cell($widths[$idx], $hHeader, $label, 1, 0, 'C', true);
			}
			$ukurSpan = array_sum(array_slice($widths, 4, 10));
			$pdf->Cell($ukurSpan, $hHeader, 'HASIL PENGUKURAN', 1, 0, 'C', true);


			$rightHeaders = [
				14 => 'Rata-Rata',
				15 => 'Ikatan',
				16 => 'Futoito'
			];
			foreach ($rightHeaders as $idx => $label) {
				$pdf->Cell($widths[$idx], $hHeader, $label, 1, 0, 'C', true);
			}

			$pdf->SetXY($x, $y + $hHeader);
		};

		$nbLines = function ($text, $width) use ($pdf) {
			$text = str_replace("\r", '', (string) $text);
			$lines = explode("\n", $text);
			$count = 0;

			foreach ($lines as $line) {
				if ($line === '') {
					$count++;
					continue;
				}
				$words = preg_split('/\s+/', $line);
				$currentLine = '';

				foreach ($words as $word) {
					$testLine = ($currentLine === '') ? $word : $currentLine . ' ' . $word;
					if ($pdf->GetStringWidth($testLine) > ($width - 2)) {
						if ($currentLine !== '') {
							$count++;
							$currentLine = $word;
						} else {
							$count++;
							$currentLine = '';
						}
					} else {
						$currentLine = $testLine;
					}
				}
				if ($currentLine !== '') {
					$count++;
				}
			}
			return max(1, $count);
		};
		$drawRow = function ($cells, $boldIndexes = []) use ($pdf, $widths, $nbLines, $drawHeader, $drawHeaderBox) {
			$maxLines = 1;
			foreach ($cells as $i => $cell) {
				$pdf->SetFont(in_array($i, $boldIndexes, true) ? 'Latob' : 'Lato', '', 8.5);
				$maxLines = max($maxLines, $nbLines($cell, $widths[$i] - 2));
			}
			$height = max(7, $maxLines * 4.5);
			if ($pdf->GetY() + $height > 195) {
				$pdf->AddPage();
				$drawHeaderBox();
				$drawHeader();
			}
			$x = $pdf->GetX();
			$y = $pdf->GetY();

			foreach ($cells as $i => $cell) {
				$w = $widths[$i];
				$pdf->Rect($x, $y, $w, $height);
				$pdf->SetFont(in_array($i, $boldIndexes, true) ? 'Latob' : 'Lato', '', 8.5);
				$align = in_array($i, [5, 7, 9, 11, 13, 14], true) ? 'R' : 'C';
				$cellLines = $nbLines($cell, $w - 2);
				$textHeight = $cellLines * 4;
				$offsetY = max(1.2, ($height - $textHeight) / 2);
				$pdf->SetXY($x + 1, $y + $offsetY);
				$pdf->MultiCell($w - 2, 4, $cell, 0, $align);

				$x += $w;
			}

			$pdf->SetXY(12, $y + $height);
		};

		$pdf->AddPage();
		$drawHeaderBox();
		$drawHeader();

		if (!empty($data)) {
			foreach ($data as $dt) {
				$jamRow  = $toPdf($dt['waktu'] ?? '');
				$nikCek  = trim((string) ($dt['noinduk_cek'] ?? ''));
				$namaCek = trim((string) strip_tags((string) datauser($dt['noinduk_cek'] ?? '')));
				$namaRow = $toPdf($nikCek . ($namaCek !== '' ? "\n" . $namaCek : ''));

				$insNo   = trim((string) ($dt['insno'] ?? ''));
				$skuTxt  = trim((string) strip_tags((string) viewsku($dt['po'] ?? '', $dt['item'] ?? '', $dt['dis'] ?? '')));
				$skuRow  = $toPdf($insNo . ($skuTxt !== '' ? "\n" . $skuTxt : ''));

				$lotCek  = trim((string) ($dt['lotcek'] ?? ''));
				$futoito = $lotCek === '' ? '' : 'Lot-' . $lotCek;

				$rowCells = [
					$jamRow,
					$namaRow,
					$skuRow,
					'',
					$toPdf(rupiah($dt['jalur1'] ?? 0, 0)),
					$toPdf(rupiah($dt['ukur1'] ?? 0, 1)),
					$toPdf(rupiah($dt['jalur2'] ?? 0, 0)),
					$toPdf(rupiah($dt['ukur2'] ?? 0, 1)),
					$toPdf(rupiah($dt['jalur3'] ?? 0, 0)),
					$toPdf(rupiah($dt['ukur3'] ?? 0, 1)),
					$toPdf(rupiah($dt['jalur4'] ?? 0, 0)),
					$toPdf(rupiah($dt['ukur4'] ?? 0, 1)),
					$toPdf(rupiah($dt['jalur5'] ?? 0, 0)),
					$toPdf(rupiah($dt['ukur5'] ?? 0, 1)),
					$toPdf(rupiah($dt['meai'] ?? 0, 1)),
					'',
					$toPdf($futoito)
				];

				$drawRow($rowCells, [14]);
			}
		} else {
			$pdf->SetFont('Lato', '', 9);
			$pdf->Cell(273, 8, '-- Data tidak Ditemukan --', 1, 1, 'C');
		}

		$pdf->Output('I', 'Laporan Cek Jala ' . $msn . '-' . $tgl . '.pdf');
	}
}
