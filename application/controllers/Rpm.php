<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rpm extends CI_Controller
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
		$this->load->model('rpmmodel');
		// $this->load->library('Pdf');
		include_once APPPATH . '/third_party/phpqrcode/qrlib.php';
	}

	public function index()
	{
		$header['header'] = [
			'menu' => 'rpm'
		];
		$data = [
			'data' => $this->rpmmodel->getdatarpmbaru(),
			'input' => $this->rpmmodel->getdatainput()
		];
		$footer['footer'] = [
			'menu' => 'rpm'
		];
		$this->load->view('layouts/header', $header);
		$this->load->view('rpm', $data);
		$this->load->view('layouts/footer', $footer);
	}
	public function clear()
	{
		$this->session->set_userdata('tglrpm', date('Y-m-d'));
		$this->session->unset_userdata('msnrpm');
		$this->session->set_userdata('nikrpm', $this->session->userdata('nik'));
		$this->session->unset_userdata('personilrpm');
		$this->session->unset_userdata('shiftrpm');
		$url = base_url() . 'rpm';
		redirect($url);
	}
	public function getmesin($formname = '')
	{
		$data['formname'] = $formname;
		$this->load->view('getmesin', $data);
	}
	public function getpersonil($mode = 0)
	{
		$data = [
			'mode' => $mode,
			'formname' => ''
		];
		$this->load->view('getpersonil', $data);
	}
	public function getpo()
	{
		$this->load->view('getpoinsno');
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
		$data = $this->rpmmodel->getdatapoinsno($kode);
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
		$grp = isset($_POST['grp']) ? $_POST['grp'] : '';
		$this->session->set_userdata('tglrpm', $tgl);
		if ($msn != '') {
			$this->session->set_userdata('msnrpm', $msn);
		} else {
			$this->session->unset_userdata('msnrpm');
		}
		if ($shift != '') {
			$this->session->set_userdata('shiftrpm', $shift);
		} else {
			$this->session->unset_userdata('shiftrpm');
		}
		if ($grp != '') {
			$this->session->set_userdata('gruprpm', $grp);
		} else {
			$this->session->unset_userdata('gruprpm');
		}
		echo 1;
	}
	public function resetmsn()
	{
		$this->session->unset_userdata('msnrpm');
		$url = base_url() . 'rpm';
		redirect($url);
	}
	public function resetpersonil($mode = 0)
	{
		if ($mode == 1) {
			$this->session->unset_userdata('nikrpm');
		} else {
			$this->session->unset_userdata('personilrpm');
		}
		$url = base_url() . 'rpm';
		redirect($url);
	}
	public function addpersonil()
	{
		$msn = isset($_POST['msn']) ? $_POST['msn'] : '';
		$mode = isset($_POST['mode']) ? $_POST['mode'] : 0;
		if ($msn != '') {
			if ($mode == 0) {
				$this->session->set_userdata('personilrpm', $msn);
			} else {
				$this->session->set_userdata('nikrpm', $msn);
			}
		} else {
			if ($mode == 0) {
				$this->session->unset_userdata('personilrpm');
			} else {
				$this->session->unset_userdata('nikrpm');
			}
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
		$xlot1 = 0;
		$xlot2 = 0;
		if (isset($data['tandalot'])) {
			$data['lot'] = $data['tandalot'] == 'on' ? 1 : 0;
		}
		if (isset($data['rpmduapuluh'])) {
			$data['rpmduapuluh'] = $data['rpmduapuluh'] == 'on' ? 1 : 0;
		}
		if (isset($data['nomor_lot'])) {
			$data['nomor_lot'] = $data['nomor_lot'];
			$xlot1 = $data['nomor_lot'];
		}
		if (isset($data['nomor_lot2'])) {
			$data['nomor_lot2'] = $data['nomor_lot2'];
			$xlot2 = $data['nomor_lot2'];
		}
		if ($xlot1 == $xlot2) {
			$data['nomor_lot2'] = 0;
		}
		if ($data['nomor_lot2'] == '') {
			$data['nomor_lot2'] = 0;
		}
		if (isset($data['rusak-tags']) && !empty($data['rusak-tags'])) {
			$selectrusak = $data['rusak-tags'];
			foreach ($selectrusak as $rusak) {
				$data['ket'] .= $rusak . ' ';
			}
		}
		unset($data['tandalot']);
		unset($data['rusak-tags']);
		unset($data['id']);
		$simpan = $this->rpmmodel->simpandata($data);
		if ($simpan) {
			$url = base_url() . 'rpm';
			redirect($url);
		}
	}
	public function editdata()
	{
		$data = $_POST;
		$xlot1 = 0;
		$xlot2 = 0;
		if (isset($data['tandalot'])) {
			$data['lot'] = $data['tandalot'] == 'on' ? 1 : 0;
		} else {
			$data['lot'] = 0;
		}
		if (isset($data['rpmduapuluh'])) {
			$data['rpmduapuluh'] = $data['rpmduapuluh'] == 'on' ? 1 : 0;
		}
		if (isset($data['nomor_lot'])) {
			$data['nomor_lot'] = $data['nomor_lot'];
			$xlot1 = $data['nomor_lot'];
		}
		if (isset($data['nomor_lot2'])) {
			$data['nomor_lot2'] = $data['nomor_lot2'];
			$xlot2 = $data['nomor_lot2'];
		}
		if ($xlot1 == $xlot2) {
			$data['nomor_lot2'] = 0;
		}
		if ($data['nomor_lot2'] == '') {
			$data['nomor_lot2'] = 0;
		}
		$data['ket'] = '';
		if (isset($data['rusak-tags']) && !empty($data['rusak-tags'])) {
			$selectrusak = $data['rusak-tags'];
			foreach ($selectrusak as $rusak) {
				$data['ket'] .= $rusak . ' ';
			}
		} else {
			$data['ket'] = '';
		}
		unset($data['tandalot']);
		unset($data['rusak-tags']);
		$simpan = $this->rpmmodel->editdata($data);
		if ($simpan) {
			$url = base_url() . 'rpm';
			redirect($url);
		}
	}
	public function hapusdata($id)
	{
		$data = $this->rpmmodel->hapusdata($id);
		if ($data) {
			$url = base_url() . 'rpm';
			redirect($url);
		}
	}
	public function reprpm()
	{
		$header['header'] = [
			'menu' => 'rpm'
		];
		$data = [
			'malam' => $this->rpmmodel->getdatarpmrep('M'),
			'personmalam' => $this->rpmmodel->getdatarpmperson('M')->row_array(),
			'pagi' => $this->rpmmodel->getdatarpmrep('P'),
			'personpagi' => $this->rpmmodel->getdatarpmperson('P')->row_array(),
			'siang' => $this->rpmmodel->getdatarpmrep('S'),
			'personsiang' => $this->rpmmodel->getdatarpmperson('S')->row_array(),
			'input' => $this->rpmmodel->getdataisi(),
			'mesin' => $this->rpmmodel->getdatamesinall(),
		];
		$footer['footer'] = [
			'menu' => 'rpm'
		];
		$this->load->view('layouts/header', $header);
		$this->load->view('reprpm', $data);
		$this->load->view('layouts/footer', $footer);
	}
	public function clearreprpm()
	{
		$this->session->set_userdata('tglreprpm', $this->session->userdata('tglrpm'));
		$this->session->set_userdata('msnreprpm', $this->session->userdata('msnrpm'));
		$url = base_url() . 'rpm/reprpm';
		redirect($url);
	}
	public function repaddsession()
	{
		$this->session->set_userdata('tglreprpm', tglmysql($_POST['tgl']));
		$this->session->set_userdata('msnreprpm', $_POST['msn']);
		echo 1;
	}

	public function export_pdf()
	{
		$msn = $this->session->userdata('msnreprpm');
		$tgl = $this->session->userdata('tglreprpm');

		if (empty($msn) || empty($tgl)) {
			show_error('Tanggal dan mesin belum dipilih atau belum di-apply.');
			return;
		}

		$malam = $this->rpmmodel->getdatarpmrep('M')->result_array();
		$pagi  = $this->rpmmodel->getdatarpmrep('P')->result_array();
		$siang = $this->rpmmodel->getdatarpmrep('S')->result_array();

		$personmalam = $this->rpmmodel->getdatarpmperson('M')->row_array();
		$personpagi  = $this->rpmmodel->getdatarpmperson('P')->row_array();
		$personsiang = $this->rpmmodel->getdatarpmperson('S')->row_array();

		$input = $this->rpmmodel->getdataisi()->result_array();

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
			$y = 12;
			$boxH = 24;


			$pdf->Rect($x, $y, 45, $boxH);
			$pdf->Image(
				FCPATH . 'assets/img/logo2.png',
				$x + 7.5,
				$y + 2,
				26,
				20
			);

			$infoWidth = 55;
			$infoX = $x + 273 - $infoWidth;
			$infoY = $y;

			$pdf->Rect($infoX, $infoY, $infoWidth, $boxH);
			$pdf->Line($infoX, $infoY + 8, $infoX + $infoWidth, $infoY + 8);
			$pdf->Line($infoX, $infoY + 16, $infoX + $infoWidth, $infoY + 16);

			$pdf->SetFont('Lato', '', 10);

			$pdf->SetXY($infoX + 1, $infoY + 2);
			$pdf->Cell(15, 4, 'No. Dok.', 0, 0);
			$pdf->Cell(35, 4, ': FM-NT-01', 0, 1);

			$pdf->SetXY($infoX + 1, $infoY + 10);
			$pdf->Cell(15, 4, 'Revisi', 0, 0);
			$pdf->Cell(35, 4, ': 4', 0, 1);

			$pdf->SetXY($infoX + 1, $infoY + 18);
			$pdf->Cell(15, 4, 'Tanggal', 0, 0);
			$pdf->Cell(35, 4, ': 21-04-2021', 0, 1);


			$titleWidth = 273 - 45 - $infoWidth;
			$pdf->Rect($x + 45, $y, $titleWidth, $boxH);
			$pdf->SetFont('Latob', '', 14);
			$pdf->SetXY($x + 45, $y + 8);
			$pdf->Cell($titleWidth, 8, 'LAPORAN HARIAN RPM', 0, 0, 'C');


			$subY = $y + $boxH;
			$pdf->Rect($x, $subY, 273, 6);
			$pdf->SetFont('Latob', '', 9);
			$pdf->SetXY($x + 2, $subY + 1);
			$pdf->Cell(135, 4, 'TANGGAL : ' . $toPdf($tgl), 0, 0, 'R');
			$pdf->Cell(135, 4, 'MESIN : ' . $toPdf($msn), 0, 1, 'L');

			$pdf->Ln(4);
		};

		$widths = [
			27,
			20,
			20,
			22,
			12,
			12,
			22,
			18,
			15,
			15,
			55,
			35
		];

		$drawHeader = function () use ($pdf, $widths) {
			$pdf->SetFont('Latob', '', 10);
			$pdf->SetFillColor(220, 220, 220);
			$pdf->SetTextColor(0, 0, 0);

			$headers = ['Waktu', 'Revolusi', '', 'Lot', 'Cek', 'PJ', 'Lot Bahan', 'Cek Meai', 'Kode', '+ 10 Rpm', 'Ket', 'No Inst'];

			$pdf->Cell($widths[0], 8, $headers[0], 1, 0, 'C', true);
			$pdf->Cell($widths[1] + $widths[2], 8, $headers[1], 1, 0, 'C', true);

			for ($i = 3; $i < count($widths); $i++) {
				$pdf->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
			}
			$pdf->Ln();
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


		$drawRow = function ($cells, $checkIndexes = []) use ($pdf, $widths, $nbLines, $drawHeader) {
			$pdf->SetFont('Lato', '', 10);
			$maxLines = 1;

			foreach ($cells as $i => $cell) {
				if (!in_array($i, $checkIndexes, true)) {
					$maxLines = max($maxLines, $nbLines($cell, $widths[$i]));
				}
			}

			$height = max(7, $maxLines * 5);

			if ($pdf->GetY() + $height > 195) {
				$pdf->AddPage();
				$drawHeader();
			}

			$x = $pdf->GetX();
			$y = $pdf->GetY();

			foreach ($cells as $i => $cell) {
				$w = $widths[$i];
				$pdf->Rect($x, $y, $w, $height);

				if (in_array($i, $checkIndexes, true)) {
					if ($cell) {
						$pdf->SetDrawColor(220, 0, 0);
						$pdf->SetLineWidth(0.6);

						$cx = $x + ($w / 2);
						$cy = $y + ($height / 2);

						$pdf->Line($cx - 1.8, $cy, $cx - 0.4, $cy + 1.5);
						$pdf->Line($cx - 0.4, $cy + 1.5, $cx + 2.2, $cy - 1.8);

						$pdf->SetDrawColor(0, 0, 0);
						$pdf->SetLineWidth(0.2);
					}
				} else {
					$align = in_array($i, [1, 2], true) ? 'R' : 'C';
					if ($i >= 10) {
						$align = 'L';
					}

					$cellLines = $nbLines($cell, $w - 2);
					$textHeight = $cellLines * 4.5;
					$offsetY = max(1, ($height - $textHeight) / 2);

					$pdf->SetXY($x + 1, $y + $offsetY);
					$pdf->MultiCell($w - 2, 4.5, $cell, 0, $align);
				}
				$x += $w;
			}

			$pdf->SetXY(12, $y + $height);
		};


		$drawShift = function ($namaShift, $jadwal, $person) use ($pdf, $toPdf, $input, $drawHeader, $drawRow, $drawHeaderBox, $widths, $nbLines) {
			$pdf->AddPage();

			$drawHeaderBox();

			$shiftLower = strtolower($namaShift);
			if ($shiftLower == 'malam') {
				$pdf->SetFillColor(220, 232, 255);
			} elseif ($shiftLower == 'pagi') {
				$pdf->SetFillColor(255, 238, 204);
			} else {
				$pdf->SetFillColor(218, 247, 218);
			}


			$pdf->SetFont('Latob', '', 12);
			$pdf->Cell(273, 7, 'SHIFT ' . strtoupper($namaShift), 1, 1, 'C', true);

			$pdf->SetFont('Latob', '', 12);
			$pdf->SetFillColor(220, 220, 220);

			$personWidths = [35, 75, 55, 35, 73];
			$personHeaders = ['No Mesin', 'Operator', 'NIK', 'Group', 'Dicek Oleh'];

			foreach ($personHeaders as $i => $label) {
				$pdf->Cell($personWidths[$i], 6, $label, 1, 0, 'C', true);
			}
			$pdf->Ln();


			$pdf->SetFont('Lato', '', 12);
			$op1 = !empty($person['noinduk_1']) ? datauser($person['noinduk_1']) : '';
			$op2 = !empty($person['noinduk_2']) ? datauser($person['noinduk_2']) : '';
			$operator = trim(strip_tags($op1)) . (!empty($op2) ? "\n" . trim(strip_tags($op2)) : '');

			$nik1 = $person['noinduk_1'] ?? '';
			$nik2 = $person['noinduk_2'] ?? '';
			$nik  = trim($nik1) . (!empty($nik2) ? "\n" . trim($nik2) : '');

			$personValues = [
				$person['mach_no'] ?? '',
				$operator,
				$nik,
				$person['grup'] ?? '',
				''
			];

			$startX = $pdf->GetX();
			$startY = $pdf->GetY();
			$maxOpLines = 1;

			foreach ($personValues as $i => $val) {
				$maxOpLines = max($maxOpLines, $nbLines($val, $personWidths[$i] - 2));
			}
			$opHeight = max(9, $maxOpLines * 4.5);

			$currX = $startX;
			foreach ($personValues as $i => $val) {
				$w = $personWidths[$i];
				$pdf->Rect($currX, $startY, $w, $opHeight);
				$pdf->SetXY($currX + 1, $startY + 1.5);
				$pdf->MultiCell($w - 2, 4.5, $toPdf($val), 0, 'C');
				$currX += $w;
			}
			$pdf->SetXY(12, $startY + $opHeight);


			$drawHeader();

			$jmlshift = 0;

			foreach ($jadwal as $mlm) {
				$ada = 0;
				foreach ($input as $inp) {
					if ($inp['id_waktu'] == $mlm['id']) {
						$ada = 1;

						$jmlshift += ($inp['rpm_akhir'] - $inp['rpm_awal']);

						$nomlot  = $inp['nomor_lot'] != 0 ? $inp['nomor_lot'] : '';
						$nomlot2 = $inp['nomor_lot2'] != 0 ? ',' . $inp['nomor_lot2'] : '';
						$lot     = $nomlot . $nomlot2;

						$awalVal  = ($inp['rpm_awal'] != 0) ? rupiah($inp['rpm_awal'], 0) : '-';
						$akhirVal = ($inp['rpm_akhir'] != 0) ? rupiah($inp['rpm_akhir'], 0) : '-';

						$isSisa  = ($mlm['waktu'] == 'SISA');

						$ket = $toPdf($inp['ket']) . (!empty($inp['ketrpm']) ? "\n" . $toPdf($inp['ketrpm']) : '');
						$noInst = $toPdf($inp['insno']) . "\n" . $toPdf(viewsku($inp['po'], $inp['item'], $inp['dis']));

						if ($isSisa) {
							$sisaVal = ($inp['rpm_akhir'] - $inp['rpm_awal']);
							if ($sisaVal < 0) $sisaVal = 0;

							$sisaLines = max(1, $nbLines($noInst, $widths[11] - 2));
							$sisaHeight = max(7, $sisaLines * 5);

							if ($pdf->GetY() + $sisaHeight > 195) {
								$pdf->AddPage();
								$drawHeader();
							}

							$sx = $pdf->GetX();
							$sy = $pdf->GetY();

							$pdf->Cell($widths[0], $sisaHeight, $toPdf($mlm['waktu']), 1, 0, 'C');
							$pdf->Cell($widths[1], $sisaHeight, '-', 1, 0, 'R');
							$pdf->Cell($widths[2], $sisaHeight, rupiah($sisaVal, 0), 1, 0, 'R');

							$pdf->SetFillColor(0, 0, 0);
							$pdf->SetTextColor(255, 255, 255);
							$pdf->SetFont('Latob', '', 9);
							$totalWidthSpan = $widths[3] + $widths[4] + $widths[5];
							$pdf->Cell($totalWidthSpan, $sisaHeight, 'TOTAL : ' . rupiah($jmlshift, 0), 1, 0, 'R', true);

							$pdf->SetTextColor(0, 0, 0);
							$pdf->SetFont('Lato', '', 9);
							for ($k = 6; $k <= 10; $k++) {
								$pdf->Cell($widths[$k], $sisaHeight, '', 1, 0, 'C');
							}

							$wInst = $widths[11];
							$pdf->Rect($pdf->GetX(), $sy, $wInst, $sisaHeight);
							$textH = $sisaLines * 4.5;
							$offY = max(1, ($sisaHeight - $textH) / 2);
							$pdf->SetXY($pdf->GetX() + 1, $sy + $offY);
							$pdf->MultiCell($wInst - 2, 4.5, $noInst, 0, 'L');

							$pdf->SetXY(12, $sy + $sisaHeight);
							$pdf->Ln(0);

							if (strtolower($namaShift) == 'siang') {
								$jmlshift = 0;
							}
						} else {
							$drawRow([
								$toPdf($mlm['waktu']),
								$awalVal,
								$akhirVal,
								$toPdf($lot),
								$inp['cek'] != 0,
								$inp['pj'] != 0,
								'', '', '',
								$inp['rpmduapuluh'] != 0,
								$ket,
								$noInst
							], [4, 5, 9]);
						}
					}
				}

				if ($ada == 0) {
					$isSisa = ($mlm['waktu'] == 'SISA');
					if ($isSisa) {
						$pdf->Cell($widths[0], 7, $toPdf($mlm['waktu']), 1, 0, 'C');
						$pdf->Cell($widths[1], 7, '-', 1, 0, 'R');
						$pdf->Cell($widths[2], 7, '-', 1, 0, 'R');

						$pdf->SetFillColor(0, 0, 0);
						$pdf->SetTextColor(255, 255, 255);
						$pdf->SetFont('Latob', '', 12);
						$totalWidthSpan = $widths[3] + $widths[4] + $widths[5];
						$pdf->Cell($totalWidthSpan, 7, 'TOTAL : ' . rupiah($jmlshift, 0), 1, 0, 'R', true);

						$pdf->SetTextColor(0, 0, 0);
						$pdf->SetFont('Lato', '', 12);
						for ($k = 6; $k < count($widths); $k++) {
							$pdf->Cell($widths[$k], 7, '', 1, 0);
						}
						$pdf->Ln();

						if (strtolower($namaShift) == 'siang') {
							$jmlshift = 0;
						}
					} else {
						$drawRow([
							$toPdf($mlm['waktu']),
							'-', '-', '', false, false, '', '', '', false, '', ''
						]);
					}
				}
			}
			$pdf->Ln(3);
		};


		$drawShift('Malam', $malam, $personmalam);
		$drawShift('Pagi', $pagi, $personpagi);
		$drawShift('Siang', $siang, $personsiang);

		$pdf->Output('I', 'Laporan-RPM ' . $msn . '-' . $tgl . '.pdf');
	}
}
